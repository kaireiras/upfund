<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectImage extends Model
{
    // Menonaktifkan timestamps bawaan (created_at & updated_at)
    // karena tidak ada di skema Anda
    public $timestamps = false;

    protected $guarded = ['id'];

    /**
     * Relasi ke tabel projects (Many-to-One)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
