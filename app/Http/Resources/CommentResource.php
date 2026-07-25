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
            'date'        => $this->date?->toISOString(),
            'project_url' => $this->project_url,
            'user'        => $this->whenLoaded('user', fn ($u) => (new UserResource($u))->resolve()),
        ];
    }
}