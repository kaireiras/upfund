<?php

namespace App\Models;

// 1. Import Trait HasApiTokens milik Sanctum
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // 2. Pasang HasApiTokens di dalam trait yang digunakan class
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'bio',
        'avatar_url',
        'bank_account_details',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function kyc()
    {
        return $this->hasOne(Kyc::class);
    }

    // Helper untuk mengecek apakah user sudah terverifikasi
    public function isKycVerified(): bool
    {
        return $this->kyc && $this->kyc->status === 'approved';
    }
}