<?php

namespace App\Filament\Resources\PharmacyShowcases;

use App\Filament\Resources\PharmacyShowcases\Pages\CreatePharmacyShowcase;
use App\Filament\Resources\PharmacyShowcases\Pages\EditPharmacyShowcase;
use App\Filament\Resources\PharmacyShowcases\Pages\ListPharmacyShowcases;
use App\Filament\Resources\PharmacyShowcases\Pages\ViewPharmacyShowcase;
use App\Filament\Resources\PharmacyShowcases\Schemas\PharmacyShowcaseForm;
use App\Filament\Resources\PharmacyShowcases\Schemas\PharmacyShowcaseInfolist;
use App\Filament\Resources\PharmacyShowcases\Tables\PharmacyShowcasesTable;
use App\Models\PharmacyShowcase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PharmacyShowcaseResource extends Resource
{
    protected static ?string $model = PharmacyShowcase::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::PresentationChartLine;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Pharmacy Showcase';

    public static function form(Schema $schema): Schema
    {
        return PharmacyShowcaseForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PharmacyShowcaseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PharmacyShowcasesTable::configure($table);
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
            'index' => ListPharmacyShowcases::route('/'),
            'create' => CreatePharmacyShowcase::route('/create'),
            'view' => ViewPharmacyShowcase::route('/{record}'),
            'edit' => EditPharmacyShowcase::route('/{record}/edit'),
        ];
    }

    public static function afterSave(Model $record, array $data): void
    {
        if ($record->is_active) {
            PharmacyShowcase::where('id', '!=', $record->id)->where('is_active', true)->update(['is_active' => false]);
        }
    }
}
