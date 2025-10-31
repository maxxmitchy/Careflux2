<?php

namespace App\Filament\Resources\Admin\ProductAlerts\Pages;

use App\Filament\Resources\Admin\ProductAlerts\ProductAlertResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductAlerts extends ListRecords
{
    protected static string $resource = ProductAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
