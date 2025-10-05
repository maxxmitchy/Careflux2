<?php

namespace App\Filament\Resources\MarketingAssets\Pages;

use App\Filament\Resources\MarketingAssets\MarketingAssetResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMarketingAsset extends ViewRecord
{
    protected static string $resource = MarketingAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
