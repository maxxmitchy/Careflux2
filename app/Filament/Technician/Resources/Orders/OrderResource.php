<?php

namespace App\Filament\Technician\Resources\Orders;

use App\Filament\Technician\Resources\Orders\Pages\CreateOrder;
use App\Filament\Technician\Resources\Orders\Pages\EditOrder;
use App\Filament\Technician\Resources\Orders\Pages\ListOrders;
use App\Filament\Technician\Resources\Orders\Pages\ViewOrder;
use App\Filament\Technician\Resources\Orders\Schemas\OrderForm;
use App\Filament\Technician\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\Technician\Resources\Orders\Tables\OrdersTable;
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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArchiveBox;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    protected static ?string $modelLabel = 'Order Fulfillment';

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
        // Technicians see orders from the same pharmacy as their linked user account
        return parent::getEloquentQuery()->where('pharmacy_id', Filament::auth()->user()->pharmacy_id);
    }
}
