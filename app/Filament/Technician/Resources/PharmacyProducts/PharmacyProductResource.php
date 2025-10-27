<?php

namespace App\Filament\Technician\Resources\PharmacyProducts;

use App\Filament\Technician\Resources\PharmacyProducts\Pages\CreatePharmacyProduct;
use App\Filament\Technician\Resources\PharmacyProducts\Pages\EditPharmacyProduct;
use App\Filament\Technician\Resources\PharmacyProducts\Pages\ListPharmacyProducts;
use App\Filament\Technician\Resources\PharmacyProducts\Schemas\PharmacyProductForm;
use App\Filament\Technician\Resources\PharmacyProducts\Tables\PharmacyProductsTable;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class PharmacyProductResource extends Resource
{
    protected static ?string $model = PharmacyProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArchiveBox;

    protected static ?string $recordTitleAttribute = 'slug';

    protected static ?string $modelLabel = 'Inventory Management';

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

    // Technicians cannot create new product listings from scratch.
    public static function canCreate(): bool
    {
        return false;
    }
}
