<?php

namespace App\Filament\Resources\EarningShowcases;

use App\Filament\Resources\EarningShowcases\Pages\CreateEarningShowcase;
use App\Filament\Resources\EarningShowcases\Pages\EditEarningShowcase;
use App\Filament\Resources\EarningShowcases\Pages\ListEarningShowcases;
use App\Filament\Resources\EarningShowcases\Schemas\EarningShowcaseForm;
use App\Filament\Resources\EarningShowcases\Tables\EarningShowcasesTable;
use App\Models\EarningShowcase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class EarningShowcaseResource extends Resource
{
    protected static ?string $model = EarningShowcase::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Pharmacist Earning Showcase';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EarningShowcaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EarningShowcasesTable::configure($table);
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
            'index' => ListEarningShowcases::route('/'),
            'create' => CreateEarningShowcase::route('/create'),
            'edit' => EditEarningShowcase::route('/{record}/edit'),
        ];
    }

    /**
     * Ensures only one showcase is active at a time.
     */
    public static function afterSave(Model $record, array $data): void
    {
        if ($record->is_active) {
            EarningShowcase::where('id', '!=', $record->id)->where('is_active', true)->update(['is_active' => false]);
        }
    }
}
