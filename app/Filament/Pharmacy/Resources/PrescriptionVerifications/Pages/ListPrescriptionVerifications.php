<?php

namespace App\Filament\Pharmacy\Resources\PrescriptionVerifications\Pages;

use App\Filament\Pharmacy\Resources\PrescriptionVerifications\PrescriptionVerificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrescriptionVerifications extends ListRecords
{
    protected static string $resource = PrescriptionVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
