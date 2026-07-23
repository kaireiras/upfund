<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isVerified = $this->isKycVerified();

        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'username'             => $this->username ?? strtolower(str_replace(' ', '', $this->name)),
            'email'                => $this->email,
            'bio'                  => $this->bio,
            'avatar_url'           => $this->avatar_url,
            'bank_account_details' => $this->bank_account_details,
            
            // Mengambil alamat dari record KYC jika ada
            'residential_address'  => $this->kyc ? $this->kyc->address : null,

            // Ringkasan Status Verification
            'is_verified'          => $isVerified,
            'verification_badge'   => $isVerified ? 'VERIFIED INVESTOR' : 'UNVERIFIED',
            'kyc_status'           => $this->kyc ? $this->kyc->status : 'unsubmitted',

            // Account Metadata
            'member_since'         => $this->created_at ? $this->created_at->format('M Y') : null, // Contoh: "Jan 2026"
            'role'                 => $this->role ?? 'User',
        ];
    }
}
