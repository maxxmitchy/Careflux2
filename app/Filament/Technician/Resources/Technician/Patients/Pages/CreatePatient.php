<?php

namespace App\Filament\Technician\Resources\Technician\Patients\Pages;

use App\Filament\Technician\Resources\Technician\Patients\PatientResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $pharmacy = Filament::auth()->user()->pharmacy;

        // Find or create a default community for the pharmacy
        $community = $pharmacy->communities()->firstOrCreate(['name' => $pharmacy->name.' Main Community']);

        $data['community_id'] = $community->id;
        $data['pharmacist_id'] = null; // Ensure patient is unassigned

        return $data;
    }
}
