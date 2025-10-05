<?php

namespace App\Filament\Pharmacy\Resources\Orders\Pages;

use App\Filament\Pharmacy\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'new' => Tab::make('New & Confirmed')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['paid', 'confirmed']))
                ->badge(static::getResource()::getNavigationBadge())
                ->badgeColor('warning'),
            'dispatched' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'dispatched')),
            'delivered' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'delivered')),
            'cancelled' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),
        ];
    }
}
