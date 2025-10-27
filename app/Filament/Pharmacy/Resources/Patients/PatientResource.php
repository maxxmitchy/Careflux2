<?php

namespace App\Filament\Pharmacy\Resources\Patients;

use App\Filament\Pharmacy\Resources\Patients\Pages\CreatePatient;
use App\Filament\Pharmacy\Resources\Patients\Pages\EditPatient;
use App\Filament\Pharmacy\Resources\Patients\Pages\ListPatients;
use App\Filament\Pharmacy\Resources\Patients\Pages\ViewPatient;
use App\Filament\Pharmacy\Resources\Patients\Schemas\PatientForm;
use App\Filament\Pharmacy\Resources\Patients\Schemas\PatientInfolist;
use App\Filament\Pharmacy\Resources\Patients\Tables\PatientsTable;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Patient\Domain\Models\Patient;
use UnitEnum;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;

    protected static ?int $navigationSort = 1;

    protected static string|UnitEnum|null $navigationGroup = 'Pharmacy Management';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return PatientForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PatientInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InteractionsRelationManager::class,
            RelationManagers\PrescriptionsRelationManager::class,
            RelationManagers\QuestionnaireInvitationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPatients::route('/'),
            'create' => CreatePatient::route('/create'),
            'view' => ViewPatient::route('/{record}'),
            'edit' => EditPatient::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Start with the base query from the parent resource.
        $query = parent::getEloquentQuery();

        // Add a non-negotiable WHERE clause to ensure the pharmacist
        // can ONLY see patients directly assigned to them.
        $query->where('pharmacist_id', Filament::auth()->id());

        return $query;
    }
}
