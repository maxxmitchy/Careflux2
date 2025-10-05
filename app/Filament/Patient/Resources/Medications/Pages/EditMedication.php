<?php

namespace App\Filament\Patient\Resources\Medications\Pages;

use App\Filament\Patient\Resources\Medications\MedicationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMedication extends EditRecord
{
    protected static string $resource = MedicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
