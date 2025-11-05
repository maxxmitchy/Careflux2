<?php

namespace App\Filament\Resources\Medications;

use App\Filament\Resources\Medications\Pages\CreateMedication;
use App\Filament\Resources\Medications\Pages\EditMedication;
use App\Filament\Resources\Medications\Pages\ListMedications;
use App\Filament\Resources\Medications\Pages\ViewMedication;
use App\Filament\Resources\Medications\Schemas\MedicationForm;
use App\Filament\Resources\Medications\Schemas\MedicationInfolist;
use App\Filament\Resources\Medications\Tables\MedicationsTable;
use App\Models\Scopes\ApprovedScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Medication\Domain\Models\Medication;
use UnitEnum;

class MedicationResource extends Resource
{
    protected static ?string $model = Medication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Beaker;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog Management';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MedicationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MedicationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VariantsRelationManager::class,
            RelationManagers\CounselingPointsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedications::route('/'),
            'create' => CreateMedication::route('/create'),
            'view' => ViewMedication::route('/{record}'),
            'edit' => EditMedication::route('/{record}/edit'),
        ];
    }

    /**
     * This method allows us to modify the base Eloquent query for the entire resource.
     */
    public static function getEloquentQuery(): Builder
    {
        // 2. Remove the global scope for this resource.
        return parent::getEloquentQuery()->withoutGlobalScope(ApprovedScope::class);
    }
}
