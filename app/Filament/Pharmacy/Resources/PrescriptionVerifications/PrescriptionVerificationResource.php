<?php

namespace App\Filament\Pharmacy\Resources\PrescriptionVerifications;

use App\Filament\Pharmacy\Resources\PrescriptionVerifications\Pages\CreatePrescriptionVerification;
use App\Filament\Pharmacy\Resources\PrescriptionVerifications\Pages\EditPrescriptionVerification;
use App\Filament\Pharmacy\Resources\PrescriptionVerifications\Pages\ListPrescriptionVerifications;
use App\Filament\Pharmacy\Resources\PrescriptionVerifications\Pages\ViewPrescriptionVerification;
use App\Filament\Pharmacy\Resources\PrescriptionVerifications\Schemas\PrescriptionVerificationForm;
use App\Filament\Pharmacy\Resources\PrescriptionVerifications\Schemas\PrescriptionVerificationInfolist;
use App\Filament\Pharmacy\Resources\PrescriptionVerifications\Tables\PrescriptionVerificationsTable;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Pharmacy\Domain\Models\PrescriptionVerification;
use UnitEnum;

class PrescriptionVerificationResource extends Resource
{
    protected static ?string $model = PrescriptionVerification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShieldCheck;

    protected static ?string $recordTitleAttribute = 'reference_code';

    protected static string|UnitEnum|null $navigationGroup = 'Daily Operations  ';

    protected static ?int $navigationSort = 3; // High priority in the sidebar

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('verifier_id', Filament::auth()->id())
            ->where('status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return PrescriptionVerificationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PrescriptionVerificationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrescriptionVerificationsTable::configure($table);
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
            'index' => ListPrescriptionVerifications::route('/'),
            'create' => CreatePrescriptionVerification::route('/create'),
            'view' => ViewPrescriptionVerification::route('/{record}'),
            'edit' => EditPrescriptionVerification::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('verifier_id', Filament::auth()->id());
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
