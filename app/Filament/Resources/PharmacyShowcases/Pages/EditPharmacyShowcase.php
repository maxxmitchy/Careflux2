<?php

namespace App\Filament\Resources\PharmacyShowcases\Pages;

use App\Filament\Resources\PharmacyShowcases\PharmacyShowcaseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPharmacyShowcase extends EditRecord
{
    protected static string $resource = PharmacyShowcaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
