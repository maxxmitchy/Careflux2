<?php

namespace App\Filament\Resources\MedicationInformation\Pages;

use App\Filament\Resources\MedicationInformation\MedicationInformationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedicationInformation extends ListRecords
{
    protected static string $resource = MedicationInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
