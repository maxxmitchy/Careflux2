<?php

namespace App\Filament\Resources\MarketingAssets;

use App\Filament\Resources\MarketingAssets\Pages\CreateMarketingAsset;
use App\Filament\Resources\MarketingAssets\Pages\EditMarketingAsset;
use App\Filament\Resources\MarketingAssets\Pages\ListMarketingAssets;
use App\Filament\Resources\MarketingAssets\Pages\ViewMarketingAsset;
use App\Filament\Resources\MarketingAssets\Schemas\MarketingAssetForm;
use App\Filament\Resources\MarketingAssets\Schemas\MarketingAssetInfolist;
use App\Filament\Resources\MarketingAssets\Tables\MarketingAssetsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Src\Marketing\Domain\Models\MarketingAsset;

class MarketingAssetResource extends Resource
{
    protected static ?string $model = MarketingAsset::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Gift;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Pharmacist Asset';

    protected static ?string $pluralModelLabel = 'Pharmacist Assets';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', false)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return MarketingAssetForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MarketingAssetInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketingAssetsTable::configure($table);
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
            'index' => ListMarketingAssets::route('/'),
            'create' => CreateMarketingAsset::route('/create'),
            'view' => ViewMarketingAsset::route('/{record}'),
            'edit' => EditMarketingAsset::route('/{record}/edit'),
        ];
    }
}
