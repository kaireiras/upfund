<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kyc;

class AdminKycController extends Controller
{
    /**
     * Melihat semua pengajuan KYC yang pending
     * GET /api/admin/kyc
     */
    public function index()
    {
        $pendingKycs = Kyc::with('user')
            ->where('status', 'pending')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $pendingKycs
        ]);
    }

    /**
     * Update status KYC (Approve / Reject)
     * POST /api/admin/kyc/{id}/verify
     */
    public function verify(Request $request, $id)
    {
        $request->validate([
            'status'           => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string'
        ]);

        $kyc = Kyc::findOrFail($id);
        $kyc->update([
            'status'           => $request->status,
            'rejection_reason' => $request->status === 'rejected' ? $request->rejection_reason : null
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "KYC status updated to {$request->status}.",
            'data'    => $kyc
        ]);
    }
}
