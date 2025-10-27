<?php

namespace App\Filament\Pharmacy\Resources\PharmacyProducts;

use App\Filament\Pharmacy\Resources\PharmacyProducts\Pages\CreatePharmacyProduct;
use App\Filament\Pharmacy\Resources\PharmacyProducts\Pages\EditPharmacyProduct;
use App\Filament\Pharmacy\Resources\PharmacyProducts\Pages\ListPharmacyProducts;
use App\Filament\Pharmacy\Resources\PharmacyProducts\Schemas\PharmacyProductForm;
use App\Filament\Pharmacy\Resources\PharmacyProducts\Tables\PharmacyProductsTable;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use UnitEnum;

class PharmacyProductResource extends Resource
{
    protected static ?string $model = PharmacyProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Pharmacy Management';

    protected static ?string $modelLabel = 'My Product';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'slug';

    public static function form(Schema $schema): Schema
    {
        return PharmacyProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PharmacyProductsTable::configure($table);
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
            'index' => ListPharmacyProducts::route('/'),
            'create' => CreatePharmacyProduct::route('/create'),
            'edit' => EditPharmacyProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('pharmacy_id', Filament::auth()->user()->pharmacy_id);
    }
}
