<?php

namespace App\Filament\Patient\Resources\Medications;

use App\Filament\Patient\Resources\Medications\Pages\CreateMedication;
use App\Filament\Patient\Resources\Medications\Pages\EditMedication;
use App\Filament\Patient\Resources\Medications\Pages\ListMedications;
use App\Filament\Patient\Resources\Medications\Schemas\MedicationForm;
use App\Filament\Patient\Resources\Medications\Tables\MedicationsTable;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Patient\Domain\Models\Prescription;

class MedicationResource extends Resource
{
    protected static ?string $model = Prescription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Beaker;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'My Medication';

    protected static ?string $pluralModelLabel = 'My Medications';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return MedicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicationsTable::configure($table);
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
            'index' => ListMedications::route('/'),
            'create' => CreateMedication::route('/create'),
            'edit' => EditMedication::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('patient_id', Filament::auth()->user()->patientProfile?->id);
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
