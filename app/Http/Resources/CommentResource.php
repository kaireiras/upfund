<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'comment' => $this->comment,
            'date'    => $this->date?->toISOString(),
            'user'    => $this->whenLoaded('user', fn ($u) => (new UserResource($u))->resolve()),
        ];
    }
}
