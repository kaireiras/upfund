<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    // Override karena custom timestamp column di migrasi kamu
    const CREATED_AT = 'date';
    const UPDATED_AT = null;

    protected $fillable = ['image_url', 'description', 'project_url'];

    // Menghubungkan ke tabel comments secara polymorphic
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'target', 'target_type', 'target_id');
    }

    // Menghubungkan ke tabel interaction secara polymorphic
    public function interactions(): MorphMany
    {
        return $this->morphMany(Interaction::class, 'target', 'target_type', 'target_id');
    }
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'post_id');
    }
}