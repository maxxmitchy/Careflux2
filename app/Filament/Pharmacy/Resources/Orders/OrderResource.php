<?php

namespace App\Filament\Pharmacy\Resources\Orders;

use App\Filament\Pharmacy\Resources\Orders\Pages\CreateOrder;
use App\Filament\Pharmacy\Resources\Orders\Pages\EditOrder;
use App\Filament\Pharmacy\Resources\Orders\Pages\ListOrders;
use App\Filament\Pharmacy\Resources\Orders\Pages\ViewOrder;
use App\Filament\Pharmacy\Resources\Orders\Schemas\OrderForm;
use App\Filament\Pharmacy\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\Pharmacy\Resources\Orders\Tables\OrdersTable;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Order\Domain\Models\Invoice;

class OrderResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingBag;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    protected static ?int $navigationSort = 1; // High priority in the sidebar

    public static function getNavigationBadge(): ?string
    {
        // Show a count of new, actionable orders
        return static::getModel()::where('pharmacy_id', Filament::auth()->user()->pharmacy_id)
            ->whereIn('status', ['paid', 'confirmed'])
            ->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('pharmacy_id', Filament::auth()->user()->pharmacy_id);
    }
}
