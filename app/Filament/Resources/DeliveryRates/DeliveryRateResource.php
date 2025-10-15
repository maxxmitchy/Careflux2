<?php

namespace App\Filament\Resources\DeliveryRates;

use App\Filament\Resources\DeliveryRates\Pages\CreateDeliveryRate;
use App\Filament\Resources\DeliveryRates\Pages\EditDeliveryRate;
use App\Filament\Resources\DeliveryRates\Pages\ListDeliveryRates;
use App\Filament\Resources\DeliveryRates\Schemas\DeliveryRateForm;
use App\Filament\Resources\DeliveryRates\Tables\DeliveryRatesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Src\Order\Domain\Models\DeliveryRate;
use UnitEnum;

class DeliveryRateResource extends Resource
{
    protected static ?string $model = DeliveryRate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Delivery Rates';

    protected static ?string $recordTitleAttribute = 'cost_kobo';

    public static function form(Schema $schema): Schema
    {
        return DeliveryRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryRatesTable::configure($table);
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
            'index' => ListDeliveryRates::route('/'),
            'create' => CreateDeliveryRate::route('/create'),
            'edit' => EditDeliveryRate::route('/{record}/edit'),
        ];
    }
}
