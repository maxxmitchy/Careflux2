<?php

namespace App\Filament\Technician\Resources\PharmacyProducts\Pages;

use App\Filament\Technician\Resources\PharmacyProducts\PharmacyProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPharmacyProduct extends EditRecord
{
    protected static string $resource = PharmacyProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
