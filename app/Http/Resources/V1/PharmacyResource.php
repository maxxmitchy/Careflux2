<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'logoUrl' => $this->logo ? asset('storage/'.$this->logo) : null,
            'location' => [
                'city' => $this->city?->name,
                'state' => $this->state?->name,
            ],
            'staff' => UserSimpleResource::collection($this->whenLoaded('users')),
        ];
    }
}
