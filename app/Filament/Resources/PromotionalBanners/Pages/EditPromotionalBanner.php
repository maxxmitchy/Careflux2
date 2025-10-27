<?php

namespace App\Filament\Resources\PromotionalBanners\Pages;

use App\Filament\Resources\PromotionalBanners\PromotionalBannerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPromotionalBanner extends EditRecord
{
    protected static string $resource = PromotionalBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
