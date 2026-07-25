<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'image_url'            => $this->image_url,
            'description'          => $this->description,
            'date'                 => $this->date?->toISOString(),
            'project_url'          => $this->project_url,
            'post_type'            => is_null($this->project_url) ? 'social_post' : 'project_post',
            'is_linked_to_project' => !is_null($this->project_url),
            'categories'           => $this->categories->pluck('title'),
            'metrics'              => [
                'likes_count'    => (int) ($this->likes_count ?? 0),
                'comments_count' => (int) ($this->comments_count ?? 0),
                'shares_count'   => (int) ($this->shares_count ?? 0),
            ],
            'comments' => $this->whenLoaded('comments', function () {
                return $this->comments->map(fn ($c) => [
                    'id'          => $c->id,
                    'comment'     => $c->comment,
                    'date'        => $c->date?->toISOString(),
                    'project_url' => $c->project_url,
                    'user'        => [
                        'id'   => $c->user?->id,
                        'name' => $c->user?->name,
                    ],
                ]);
            }),
        ];
    }
}