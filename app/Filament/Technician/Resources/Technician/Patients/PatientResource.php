<?php

namespace App\Filament\Technician\Resources\Technician\Patients;

use App\Filament\Technician\Resources\Technician\Patients\Pages\CreatePatient;
use App\Filament\Technician\Resources\Technician\Patients\Pages\EditPatient;
use App\Filament\Technician\Resources\Technician\Patients\Pages\ListPatients;
use App\Filament\Technician\Resources\Technician\Patients\Schemas\PatientForm;
use App\Filament\Technician\Resources\Technician\Patients\Tables\PatientsTable;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Patient\Domain\Models\Patient;
use Src\Pharmacy\Domain\Models\Pharmacy;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserPlus;

    public static function form(Schema $schema): Schema
    {
        return PatientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();
        $pharmacyId = $user->pharmacy_id;

        return parent::getEloquentQuery()
            // --- THIS IS THE FIX ---
            ->whereHas('community', function (Builder $query) use ($pharmacyId) {
                // Query the polymorphic relationship correctly
                $query->where('owner_id', $pharmacyId)
                    ->where('owner_type', Pharmacy::class);
            });
        // --- END OF FIX ---
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPatients::route('/'),
            'create' => CreatePatient::route('/create'),
            'edit' => EditPatient::route('/{record}/edit'),
        ];
    }
}
