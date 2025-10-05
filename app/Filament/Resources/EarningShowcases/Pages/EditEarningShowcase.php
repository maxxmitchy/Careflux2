<?php

namespace App\Filament\Resources\EarningShowcases\Pages;

use App\Filament\Resources\EarningShowcases\EarningShowcaseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEarningShowcase extends EditRecord
{
    protected static string $resource = EarningShowcaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
