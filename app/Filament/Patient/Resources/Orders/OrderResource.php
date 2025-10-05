<?php

namespace App\Filament\Patient\Resources\Orders;

use App\Filament\Patient\Resources\Orders\Pages\CreateOrder;
use App\Filament\Patient\Resources\Orders\Pages\EditOrder;
use App\Filament\Patient\Resources\Orders\Pages\ListOrders;
use App\Filament\Patient\Resources\Orders\Pages\ViewOrder;
use App\Filament\Patient\Resources\Orders\Schemas\OrderForm;
use App\Filament\Patient\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\Patient\Resources\Orders\Tables\OrdersTable;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Src\Order\Domain\Models\Invoice;

class OrderResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingBag;

    protected static ?string $modelLabel = 'My Order';

    protected static ?string $pluralModelLabel = 'My Orders';

    protected static ?int $navigationSort = 2;

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
        return parent::getEloquentQuery()->where('patient_id', Filament::auth()->user()->patientProfile?->id);
    }

    // Patients cannot create or edit orders directly.
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }
}
