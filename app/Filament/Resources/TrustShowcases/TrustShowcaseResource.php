<?php

namespace App\Filament\Resources\TrustShowcases;

use App\Filament\Resources\TrustShowcases\Pages\CreateTrustShowcase;
use App\Filament\Resources\TrustShowcases\Pages\EditTrustShowcase;
use App\Filament\Resources\TrustShowcases\Pages\ListTrustShowcases;
use App\Filament\Resources\TrustShowcases\Schemas\TrustShowcaseForm;
use App\Filament\Resources\TrustShowcases\Tables\TrustShowcasesTable;
use App\Models\TrustShowcase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TrustShowcaseResource extends Resource
{
    protected static ?string $model = TrustShowcase::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TrustShowcaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrustShowcasesTable::configure($table);
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
            'index' => ListTrustShowcases::route('/'),
            'create' => CreateTrustShowcase::route('/create'),
            'edit' => EditTrustShowcase::route('/{record}/edit'),
        ];
    }
}
