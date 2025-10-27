<?php

namespace App\Filament\Resources\PromotionalBanners\Pages;

use App\Filament\Resources\PromotionalBanners\PromotionalBannerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPromotionalBanner extends ViewRecord
{
    protected static string $resource = PromotionalBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
