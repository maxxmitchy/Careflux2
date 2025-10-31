<?php

namespace App\Filament\Resources\Admin\PharmacistActionLogs\Pages;

use App\Filament\Resources\Admin\PharmacistActionLogs\PharmacistActionLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPharmacistActionLog extends EditRecord
{
    protected static string $resource = PharmacistActionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
