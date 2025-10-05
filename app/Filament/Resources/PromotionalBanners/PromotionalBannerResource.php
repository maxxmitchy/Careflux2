<?php

namespace App\Filament\Resources\PromotionalBanners;

use App\Filament\Resources\PromotionalBanners\Pages\CreatePromotionalBanner;
use App\Filament\Resources\PromotionalBanners\Pages\EditPromotionalBanner;
use App\Filament\Resources\PromotionalBanners\Pages\ListPromotionalBanners;
use App\Filament\Resources\PromotionalBanners\Pages\ViewPromotionalBanner;
use App\Filament\Resources\PromotionalBanners\Schemas\PromotionalBannerForm;
use App\Filament\Resources\PromotionalBanners\Schemas\PromotionalBannerInfolist;
use App\Filament\Resources\PromotionalBanners\Tables\PromotionalBannersTable;
use App\Models\PromotionalBanner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PromotionalBannerResource extends Resource
{
    protected static ?string $model = PromotionalBanner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Megaphone;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PromotionalBannerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PromotionalBannerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromotionalBannersTable::configure($table);
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
            'index' => ListPromotionalBanners::route('/'),
            'create' => CreatePromotionalBanner::route('/create'),
            'view' => ViewPromotionalBanner::route('/{record}'),
            'edit' => EditPromotionalBanner::route('/{record}/edit'),
        ];
    }
}
