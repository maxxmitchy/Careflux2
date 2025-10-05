<?php

namespace App\Filament\Resources\MarketingAssets\Pages;

use App\Filament\Resources\MarketingAssets\MarketingAssetResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMarketingAsset extends EditRecord
{
    protected static string $resource = MarketingAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
