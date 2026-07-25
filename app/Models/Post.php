<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'datetime',
    ];

    protected $fillable = ['image_url', 'description', 'project_url'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_url', 'id');
    }

    public function interactions(): MorphMany
    {
        return $this->morphMany(Interaction::class, 'target');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'target');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'post_id');
    }
}
