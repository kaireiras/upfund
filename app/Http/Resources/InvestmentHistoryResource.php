<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvestmentHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user'        => new UserResource($this->whenLoaded('user')),
            'project'     => new ProjectResource($this->whenLoaded('project')),
            'transaction' => [
                'id'           => $this->transaction?->id,
                'order_id'     => $this->transaction?->order_id,
                'amount'       => $this->transaction?->amount,
                'payment_type' => $this->transaction?->payment_type,
                'status'       => $this->transaction?->status,
                'date'         => $this->transaction?->date,
            ],
        ];
    }
}
