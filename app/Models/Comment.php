<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    // Custom timestamp sesuai migrasi kamu
    const CREATED_AT = 'date';
    const UPDATED_AT = null;

    protected $fillable = [
        'target_id',
        'target_type',
        'comment',
        'user_id',
        'project_url',
        'date'
    ];

    // Relasi ke User pembuat komentar
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}