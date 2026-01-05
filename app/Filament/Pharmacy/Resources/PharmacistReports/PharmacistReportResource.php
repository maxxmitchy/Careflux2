<?php

namespace App\Filament\Pharmacy\Resources\PharmacistReports;

use App\Filament\Pharmacy\Resources\PharmacistReports\Pages\CreatePharmacistReport;
use App\Filament\Pharmacy\Resources\PharmacistReports\Pages\EditPharmacistReport;
use App\Filament\Pharmacy\Resources\PharmacistReports\Pages\ListPharmacistReports;
use App\Filament\Pharmacy\Resources\PharmacistReports\Pages\ViewPharmacistReport;
use App\Filament\Pharmacy\Resources\PharmacistReports\Schemas\PharmacistReportForm;
use App\Filament\Pharmacy\Resources\PharmacistReports\Schemas\PharmacistReportInfolist;
use App\Filament\Pharmacy\Resources\PharmacistReports\Tables\PharmacistReportsTable;
use App\Models\PharmacistReport;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PharmacistReportResource extends Resource
{
    protected static ?string $model = PharmacistReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentChartBar;

    protected static ?string $recordTitleAttribute = 'biggest_win';

    protected static ?string $navigationLabel = 'Weekly Reports';

    protected static string|UnitEnum|null $navigationGroup = 'Management & Reporting';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PharmacistReportForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PharmacistReportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PharmacistReportsTable::configure($table);
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
            'index' => ListPharmacistReports::route('/'),
            'create' => CreatePharmacistReport::route('/create'),
            'view' => ViewPharmacistReport::route('/{record}'),
            'edit' => EditPharmacistReport::route('/{record}/edit'),
        ];
    }

    public static function mutateQueryUsing(Builder $query): Builder
    {
        return $query->where('user_id', Filament::auth()->id());
    }
}
