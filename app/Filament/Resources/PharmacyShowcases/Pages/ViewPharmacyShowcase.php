<?php

namespace App\Filament\Resources\PharmacyShowcases\Pages;

use App\Filament\Resources\PharmacyShowcases\PharmacyShowcaseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPharmacyShowcase extends ViewRecord
{
    protected static string $resource = PharmacyShowcaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
