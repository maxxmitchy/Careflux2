<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // We use camelCase for JSON API conventions
            'id' => $this->id,
            'fullName' => $this->full_name,
            'phone' => $this->phone,
            'dateOfBirth' => $this->date_of_birth,
            'gender' => $this->gender,
            'locationArea' => $this->location_area,
            'healthProfile' => [
                'takesRegularMedications' => $this->takes_regular_medications,
                'medicationList' => $this->medication_list,
                'knownHealthConditions' => $this->known_health_conditions,
            ],
            'careTeam' => [
                'pharmacist' => new UserSimpleResource($this->whenLoaded('pharmacist')),
                'community' => new CommunitySimpleResource($this->whenLoaded('community')),
            ],
            'createdAt' => $this->created_at,
        ];
    }
}
