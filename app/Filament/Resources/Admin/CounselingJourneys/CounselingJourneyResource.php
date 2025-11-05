<?php

namespace App\Filament\Resources\Admin\CounselingJourneys;

use App\Filament\Resources\Admin\CounselingJourneys\Pages\CreateCounselingJourney;
use App\Filament\Resources\Admin\CounselingJourneys\Pages\EditCounselingJourney;
use App\Filament\Resources\Admin\CounselingJourneys\Pages\ListCounselingJourneys;
use App\Filament\Resources\Admin\CounselingJourneys\Schemas\CounselingJourneyForm;
use App\Filament\Resources\Admin\CounselingJourneys\Tables\CounselingJourneysTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Src\Content\Domain\Models\CounselingJourney;
use UnitEnum;

class CounselingJourneyResource extends Resource
{
    protected static ?string $model = CounselingJourney::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChatBubbleBottomCenterText;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Counseling Journey';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CounselingJourneyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CounselingJourneysTable::configure($table);
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
            'index' => ListCounselingJourneys::route('/'),
            'create' => CreateCounselingJourney::route('/create'),
            'edit' => EditCounselingJourney::route('/{record}/edit'),
        ];
    }
}
