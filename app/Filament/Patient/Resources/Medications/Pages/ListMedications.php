<?php

namespace App\Filament\Patient\Resources\Medications\Pages;

use App\Filament\Patient\Resources\Medications\MedicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMedications extends ListRecords
{
    protected static string $resource = MedicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
