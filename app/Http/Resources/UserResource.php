<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Representasi publik user. Sengaja membatasi field agar tidak
     * membocorkan kolom sensitif (password, email, role, is_verified).
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'username' => $this->username,
            'name'     => $this->name,
        ];
    }
}
