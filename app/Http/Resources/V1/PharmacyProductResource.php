<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'stock' => $this->stock,
            'nafdacNumber' => $this->nafdac_number,
            'slug' => $this->slug,
            'pharmacy' => new PharmacySimpleResource($this->whenLoaded('pharmacy')),
            'medication' => new MedicationResource($this->whenLoaded('medicationVariant', function () {
                return $this->medicationVariant->medication;
            })),
            'variant' => new MedicationVariantResource($this->whenLoaded('medicationVariant')),
        ];
    }
}
