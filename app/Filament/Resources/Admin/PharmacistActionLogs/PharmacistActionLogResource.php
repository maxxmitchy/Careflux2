<?php

namespace App\Filament\Resources\Admin\PharmacistActionLogs;

use App\Filament\Resources\Admin\PharmacistActionLogs\Pages\CreatePharmacistActionLog;
use App\Filament\Resources\Admin\PharmacistActionLogs\Pages\EditPharmacistActionLog;
use App\Filament\Resources\Admin\PharmacistActionLogs\Pages\ListPharmacistActionLogs;
use App\Filament\Resources\Admin\PharmacistActionLogs\Schemas\PharmacistActionLogForm;
use App\Filament\Resources\Admin\PharmacistActionLogs\Tables\PharmacistActionLogsTable;
use App\Models\PharmacistActionLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PharmacistActionLogResource extends Resource
{
    protected static ?string $model = PharmacistActionLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentMagnifyingGlass;

    protected static ?string $recordTitleAttribute = 'description';

    protected static string|UnitEnum|null $navigationGroup = 'Auditing';

    protected static ?string $modelLabel = 'Pharmacist Action Log';

    public static function form(Schema $schema): Schema
    {
        return PharmacistActionLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PharmacistActionLogsTable::configure($table);
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
            'index' => ListPharmacistActionLogs::route('/'),
            // 'create' => CreatePharmacistActionLog::route('/create'),
            // 'edit' => EditPharmacistActionLog::route('/{record}/edit'),
        ];
    }

    /**
     * Disable creation and editing. This is an immutable log.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }
}
