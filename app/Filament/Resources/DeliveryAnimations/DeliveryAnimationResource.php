<?php

namespace App\Filament\Resources\DeliveryAnimations;

use App\Filament\Resources\DeliveryAnimations\Pages\CreateDeliveryAnimation;
use App\Filament\Resources\DeliveryAnimations\Pages\EditDeliveryAnimation;
use App\Filament\Resources\DeliveryAnimations\Pages\ListDeliveryAnimations;
use App\Filament\Resources\DeliveryAnimations\Schemas\DeliveryAnimationForm;
use App\Filament\Resources\DeliveryAnimations\Tables\DeliveryAnimationsTable;
use App\Models\DeliveryAnimation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeliveryAnimationResource extends Resource
{
    protected static ?string $model = DeliveryAnimation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ServerStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DeliveryAnimationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryAnimationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StepsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryAnimations::route('/'),
            'create' => CreateDeliveryAnimation::route('/create'),
            'edit' => EditDeliveryAnimation::route('/{record}/edit'),
        ];
    }
}
