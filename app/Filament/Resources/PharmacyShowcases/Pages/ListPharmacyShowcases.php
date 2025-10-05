<?php

namespace App\Filament\Resources\PharmacyShowcases\Pages;

use App\Filament\Resources\PharmacyShowcases\PharmacyShowcaseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPharmacyShowcases extends ListRecords
{
    protected static string $resource = PharmacyShowcaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
