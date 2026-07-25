<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'target_id',
        'target_type',
        'comment',
        'user_id',
        'project_url',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    // Polymorphic target (bisa Project atau Post)
    public function target(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'target_type', 'target_id');
    }

    // Relasi ke User pembuat komentar
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_url', 'id');
    }
}
