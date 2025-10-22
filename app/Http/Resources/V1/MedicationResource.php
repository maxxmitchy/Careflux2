<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'genericName' => $this->generic_name,
            'description' => $this->description,
            'imageUrl' => $this->image ? asset('storage/'.$this->image) : null,
            'isPrescription' => $this->is_prescription,
            'variants' => MedicationVariantResource::collection($this->whenLoaded('variants')),

            'categories' => CategorySimpleResource::collection($this->whenLoaded('categories')),
        ];
    }
}
