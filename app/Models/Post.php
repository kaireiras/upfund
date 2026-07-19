<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_url', 'id');
    }

    // Polymorphic Relations
    public function interactions(): MorphMany
    {
        return $this->morphMany(Interaction::class, 'target');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'target');
    }
}
