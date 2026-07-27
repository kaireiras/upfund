<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\InvestmentHistory;
class TransactionController extends Controller
{
    public function __construct()
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Generate Payment & Get Snap Token
     * POST /api/transactions
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'amount'     => 'required|integer|min:10000', // Nilai investasi murni (Contoh: 20.000.000)
        ]);

        $user = $request->user();
        $project = Project::findOrFail($request->project_id);

        // 1. Kalkulasi Sesuai UI (Investment Amount + Platform Fee 10%)
        $investmentAmount = (int) $request->amount;
        $platformFeePercentage = 0.10; // 10% Sesuai UI
        $platformFee = (int) ($investmentAmount * $platformFeePercentage);
        $grandTotal = $investmentAmount + $platformFee; // Total yang dibayar ke Midtrans (22.000.000)

        // Format Order ID Unik
        $orderId = 'TRX-' . time() . '-' . rand(100, 999);

        // 2. Simpan Transaksi ke Database
        // Catatan: 'amount' menyimpan nilai investasi murni agar mudah menambahkan ke collected_funds nanti
        $transaction = Transaction::create([
            'order_id'   => $orderId,
            'user_id'    => $user->id,
            'project_id' => $project->id,
            'amount'     => $investmentAmount, // Rp 20.000.000
            'status'     => 'pending',
            'date'       => now(),
        ]);

        // 3. Rincian Item Payload untuk Midtrans (Pengguna melihat breakdown biaya di pop-up Midtrans)
        $params = [
            'transaction_details' => [
                'order_id'     => $transaction->order_id,
                'gross_amount' => $grandTotal, // Total Akhir (22.000.000)
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
            ],
            'item_details' => [
                [
                    'id'       => 'INV-' . $project->id,
                    'price'    => $investmentAmount,
                    'quantity' => 1,
                    'name'     => substr('Investment in ' . $project->title, 0, 50),
                ],
                [
                    'id'       => 'FEE-10',
                    'price'    => $platformFee,
                    'quantity' => 1,
                    'name'     => 'Platform Fee (10%)',
                ]
            ]
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $transaction->update(['snap_token' => $snapToken]);

            return response()->json([
                'status'        => 'success',
                'message'       => 'Payment generated successfully',
                'snap_token'    => $snapToken,
                'breakdown'     => [
                    'investment_amount' => $investmentAmount,
                    'platform_fee'      => $platformFee,
                    'grand_total'       => $grandTotal,
                ],
                'data'          => $transaction->load('project')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * Webhook/Notification Callback dari Midtrans
 * POST /api/midtrans/notification
 */
    public function notificationHandler(Request $request)
    {
        try {
            // 1. Coba inisialisasi SDK Midtrans
            try {
                $notif = new \Midtrans\Notification();
                $transactionStatus = $notif->transaction_status ?? null;
                $type              = $notif->payment_type ?? null;
                $orderId           = $notif->order_id ?? null;
                $fraud             = $notif->fraud_status ?? null;
            } catch (\Exception $e) {
                $orderId = null;
            }

            // 2. Fallback: Jika SDK mengembalikan null (misal saat testing via Postman), ambil dari Request Input
            if (!$orderId) {
                $transactionStatus = $request->input('transaction_status');
                $type              = $request->input('payment_type');
                $orderId           = $request->input('order_id');
                $fraud             = $request->input('fraud_status');
            }

            // Validasi jika order_id tetap tidak ditemukan
            if (!$orderId) {
                return response()->json([
                    'status'  => 'error', 
                    'message' => 'Invalid notification payload: order_id is missing'
                ], 400);
            }

            // 3. Cari transaksi berdasarkan order_id
            $transaction = Transaction::where('order_id', $orderId)->firstOrFail();

            // 4. Handle Status Pembayaran
            if ($transactionStatus == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $transaction->update(['status' => 'processing']);
                    } else {
                        $this->markAsPaid($transaction, $type);
                    }
                }
            } else if ($transactionStatus == 'settlement') {
                // Pembayaran Berhasil
                $this->markAsPaid($transaction, $type);
            } else if ($transactionStatus == 'pending') {
                $transaction->update(['status' => 'pending']);
            } else if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $transaction->update(['status' => 'failed']);
            }

            return response()->json(['status' => 'success', 'message' => 'Notification processed']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper Function jika status Paid
     */
    private function markAsPaid(Transaction $transaction, string $paymentType)
    {
        DB::transaction(function () use ($transaction, $paymentType) {
            if ($transaction->status !== 'paid') {
                $transaction->update([
                    'status'       => 'paid',
                    'payment_type' => $paymentType,
                ]);

                // Increment collected funds hanya sekali saat perubahan status
                Project::where('id', $transaction->project_id)
                    ->increment('collected_funds', $transaction->amount);
            }
            InvestmentHistory::firstOrCreate([
                'transaction_id' => $transaction->id,
            ], [
                'project_id' => $transaction->project_id,
                'user_id'    => $transaction->user_id,
            ]);
        });
    }
}