<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image_url,
            'description' => $this->description,
            'date' => $this->date,
            
            // Logic Penentu: Jika project_url null, maka type = social_post
            'project_url' => $this->project_url,
            'post_type' => is_null($this->project_url) ? 'social_post' : 'project_post',
            'is_linked_to_project' => !is_null($this->project_url),

            // ================= TAMBAHAN LOGIC KATEGORI =================
            // Memetakan koleksi kategori agar mengeluarkan array string nama categories langsung (misal: ["software", "iot"])
            'categories' => $this->categories->pluck('title'),
            // ==========================================================

            'metrics' => [
                'likes_count' => (int) $this->likes_count,
                'comments_count' => (int) $this->comments_count,
                'shares_count' => (int) $this->shares_count,
            ],
            
            // Memuat komentar jika dipanggil dengan eager loading
            'comments' => $this->whenLoaded('comments', function() {
                return $this->comments->map(function($comment) {
                    return [
                        'id' => $comment->id,
                        'comment' => $comment->comment,
                        'date' => $comment->date,
                        'project_url' => $comment->project_url,
                        'user' => [
                            'id' => $comment->user?->id,
                            'name' => $comment->user?->name,
                        ]
                    ];
                });
            }),
        ];
    }
}