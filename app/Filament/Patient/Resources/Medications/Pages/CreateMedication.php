<?php

namespace App\Filament\Patient\Resources\Medications\Pages;

use App\Filament\Patient\Resources\Medications\MedicationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMedication extends CreateRecord
{
    protected static string $resource = MedicationResource::class;
}
