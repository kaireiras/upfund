<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kyc;

class KycController extends Controller
{
    /**
     * Mengecek Status KYC User yang sedang Login
     * GET /api/kyc/status
     */
    public function status(Request $request)
    {
        $kyc = $request->user()->kyc;

        if (!$kyc) {
            return response()->json([
                'status' => 'unsubmitted',
                'data'   => null
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $kyc
        ]);
    }

    /**
     * Submit Dokumen KYC Baru / Upload Ulang
     * POST /api/kyc
     */
    public function store(Request $request)
    {
        $user = $request->user();

        //cek jika sudah pending atau approved
        // Cek jika user sudah pernah submit dan statusnya masih pending atau sudah approved
        if ($user->kyc && in_array($user->kyc->status, ['pending', 'approved'])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Your KYC is currently under review or already verified.'
            ], 400);
        }

        $request->validate([
            'company_document' => 'required|file|mimes:pdf|max:5120', // Maks 5MB
            'bank_document'    => 'required|file|mimes:pdf|max:5120',
            'address'          => 'required|string|max:1024'
        ]);

        $companyPath = $request->file('company_document')->store('kyc/company', 'public');
        $bankPath    = $request->file('bank_document')->store('kyc/bank', 'public');

        //Simpan record KYC
        $kyc = Kyc::create([
            'user_id'              => $user->id,
            'company_document_url' => asset('storage/' . $companyPath),
            'bank_document_url'    => asset('storage/' . $bankPath),
            'address'              => $request->address,
            'status'               => 'pending',
            'rejection_reason'     => null
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'KYC submitted successfully and is now under review.',
            'data'    => $kyc
        ], 201);
    }
}