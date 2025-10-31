<?php

namespace App\Filament\Resources\Admin\PharmacistActionLogs\Pages;

use App\Filament\Resources\Admin\PharmacistActionLogs\PharmacistActionLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPharmacistActionLogs extends ListRecords
{
    protected static string $resource = PharmacistActionLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
