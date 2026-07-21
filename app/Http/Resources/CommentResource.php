<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'target_id'   => $this->target_id,
            'target_type' => $this->target_type,
            'comment'     => $this->comment,
            'date'        => $this->date,
            'project_url' => $this->project_url,
            'user'        => [
                'id'         => $this->user?->id,
                'name'       => $this->user?->name,
                'avatar_url' => $this->user?->avatar_url ?? null,
            ]
        ];
    }
}