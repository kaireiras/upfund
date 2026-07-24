<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'user_id'         => $this->user_id,
            'title'           => $this->title,
            'description'     => $this->description,
            'date'            => $this->date,
            'video_url'       => $this->video_url,
            'valuation'       => $this->valuation,
            'collected_funds' => $this->collected_funds,
            'investment_url'  => $this->investment_url,

            // comments_count hanya muncul saat query memakai withCount('comments')
            'comments_count'  => $this->whenCounted('comments'),

            // Agregat interaksi & investor — dihitung via withCount/withSum di layer
            // Service (bukan query manual di Resource). Default 0 bila query dasar
            // belum menyertakannya (mis. withSum bisa mengembalikan null tanpa baris).
            'likes_count'     => (int) ($this->likes_count ?? 0),
            'shares_count'    => (int) ($this->shares_count ?? 0),
            'investors_count' => (int) ($this->investors_count ?? 0),

            // Relasi hanya disertakan bila di-eager-load (mencegah N+1 accidental)
            'user'            => new UserResource($this->whenLoaded('user')),
            'categories'      => CategoryResource::collection($this->whenLoaded('categories')),
            'images'          => ProjectImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
