<?php

namespace App\Filament\Resources\Admin\ProductAlerts;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Src\Pharmacovigilance\Domain\Models\ProductAlert;
use App\Filament\Resources\Admin\ProductAlerts\Pages\EditProductAlert;
use App\Filament\Resources\Admin\ProductAlerts\Pages\ListProductAlerts;
use App\Filament\Resources\Admin\ProductAlerts\Pages\CreateProductAlert;
use App\Filament\Resources\Admin\ProductAlerts\Schemas\ProductAlertForm;
use App\Filament\Resources\Admin\ProductAlerts\Tables\ProductAlertsTable;

class ProductAlertResource extends Resource
{
    protected static ?string $model = ProductAlert::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Megaphone;

    protected static ?string $recordTitleAttribute = 'type';

    public static function form(Schema $schema): Schema
    {
        return ProductAlertForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductAlertsTable::configure($table);
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
            'index' => ListProductAlerts::route('/'),
            'create' => CreateProductAlert::route('/create'),
            'edit' => EditProductAlert::route('/{record}/edit'),
        ];
    }
}
