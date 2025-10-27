<?php

namespace App\Filament\Resources\DeliveryAnimations\Pages;

use App\Filament\Resources\DeliveryAnimations\DeliveryAnimationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryAnimation extends EditRecord
{
    protected static string $resource = DeliveryAnimationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
