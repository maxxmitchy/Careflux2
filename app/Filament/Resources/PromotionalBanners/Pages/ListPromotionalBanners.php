<?php

namespace App\Filament\Resources\PromotionalBanners\Pages;

use App\Filament\Resources\PromotionalBanners\PromotionalBannerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPromotionalBanners extends ListRecords
{
    protected static string $resource = PromotionalBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
