<?php

namespace App\Filament\Resources\MarketingAssets\Pages;

use App\Filament\Resources\MarketingAssets\MarketingAssetResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMarketingAssets extends ListRecords
{
    protected static string $resource = MarketingAssetResource::class;

    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('Pending Review')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(static::getResource()::getNavigationBadge())
                ->badgeColor('warning'),
            'active' => Tab::make('Active & Live')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),
            'all' => Tab::make('All'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'pending';
    }
}
