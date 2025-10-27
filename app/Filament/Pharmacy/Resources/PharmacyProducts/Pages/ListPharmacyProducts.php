<?php

namespace App\Filament\Pharmacy\Resources\PharmacyProducts\Pages;

use App\Filament\Pharmacy\Resources\PharmacyProducts\PharmacyProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPharmacyProducts extends ListRecords
{
    protected static string $resource = PharmacyProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
