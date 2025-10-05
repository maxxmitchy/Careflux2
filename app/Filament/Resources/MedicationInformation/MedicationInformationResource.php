<?php

namespace App\Filament\Resources\MedicationInformation;

use App\Filament\Resources\MedicationInformation\Pages\CreateMedicationInformation;
use App\Filament\Resources\MedicationInformation\Pages\EditMedicationInformation;
use App\Filament\Resources\MedicationInformation\Pages\ListMedicationInformation;
use App\Filament\Resources\MedicationInformation\Pages\ViewMedicationInformation;
use App\Filament\Resources\MedicationInformation\Schemas\MedicationInformationForm;
use App\Filament\Resources\MedicationInformation\Schemas\MedicationInformationInfolist;
use App\Filament\Resources\MedicationInformation\Tables\MedicationInformationTable;
use App\Models\MedicationInformation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MedicationInformationResource extends Resource
{
    protected static ?string $model = MedicationInformation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MedicationInformationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MedicationInformationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicationInformationTable::configure($table);
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
            'index' => ListMedicationInformation::route('/'),
            'create' => CreateMedicationInformation::route('/create'),
            'view' => ViewMedicationInformation::route('/{record}'),
            'edit' => EditMedicationInformation::route('/{record}/edit'),
        ];
    }
}
