<?php

namespace App\Filament\Resources\DeliveryRates\Pages;

use App\Filament\Resources\DeliveryRates\DeliveryRateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryRates extends ListRecords
{
    protected static string $resource = DeliveryRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
