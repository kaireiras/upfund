<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'date' => 'datetime',
        'is_verified' => 'boolean',
    ];

    public function kyc(): HasOne
    {
        return $this->hasOne(KnowYourCustomer::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function investmentHistories(): HasMany
    {
        return $this->hasMany(InvestmentHistory::class);
    }
}
