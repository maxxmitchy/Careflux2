<?php

namespace App\Filament\Resources\DeliveryAnimations\Pages;

use App\Filament\Resources\DeliveryAnimations\DeliveryAnimationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryAnimations extends ListRecords
{
    protected static string $resource = DeliveryAnimationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
