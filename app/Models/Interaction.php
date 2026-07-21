<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interaction extends Model
{
    // Karena nama tabel di migrasi kamu adalah singular ('interaction')
    protected $table = 'interaction';

    const CREATED_AT = 'date';
    const UPDATED_AT = null;

    protected $fillable = ['target_id', 'target_type', 'like', 'share', 'not_interested', 'user_id'];

    public function target(): MorphTo
    {
        return $this->morphTo(null, 'target_type', 'target_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}