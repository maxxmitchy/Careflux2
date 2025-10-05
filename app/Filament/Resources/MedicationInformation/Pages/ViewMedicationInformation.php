<?php

namespace App\Filament\Resources\MedicationInformation\Pages;

use App\Filament\Resources\MedicationInformation\MedicationInformationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMedicationInformation extends ViewRecord
{
    protected static string $resource = MedicationInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
