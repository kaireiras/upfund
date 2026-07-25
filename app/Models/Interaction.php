<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Interaction extends Model
{
    protected $table = 'interaction';
    public $timestamps = false;

    protected $fillable = ['target_id', 'target_type', 'like', 'share', 'not_interested', 'user_id'];

    protected $casts = [
        'date' => 'datetime',
        'like' => 'boolean',
        'not_interested' => 'boolean',
    ];

    public function target(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'target_type', 'target_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
