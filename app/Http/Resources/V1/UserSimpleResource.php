<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSimpleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->is_manager ? 'Manager' : ($this->is_pharmacist ? 'Pharmacist' : 'Technician'),
            'avatarUrl' => $this->avatar_url ? asset('storage/'.$this->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($this->name),
            'publicProfileId' => $this->public_profile_id,
        ];
    }
}
