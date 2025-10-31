<?php

namespace App\Filament\Resources\Admin\ProductAlerts\Pages;

use App\Filament\Resources\Admin\ProductAlerts\ProductAlertResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductAlert extends EditRecord
{
    protected static string $resource = ProductAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
