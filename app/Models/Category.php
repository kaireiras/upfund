<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_categories');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
