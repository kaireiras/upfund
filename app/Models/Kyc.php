<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kyc extends Model
{
    protected $table = 'know_your_customer';

    protected $fillable = [
        'user_id',
        'company_document_url',
        'bank_document_url',
        'address',
        'status',
        'rejection_reason'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}