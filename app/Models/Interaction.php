<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Interaction extends Model
{
    protected $table = 'interaction'; // Singular di database
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'datetime',
        'like' => 'boolean',
        'not_interested' => 'boolean',
    ];

    // Polymorphic target (bisa Project atau Post)
    public function target(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'target_type', 'target_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
