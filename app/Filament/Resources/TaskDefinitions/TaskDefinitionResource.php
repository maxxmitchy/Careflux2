<?php

namespace App\Filament\Resources\TaskDefinitions;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Src\Gamification\Domain\Models\TaskDefinition;
use App\Filament\Resources\TaskDefinitions\Pages\EditTaskDefinition;
use App\Filament\Resources\TaskDefinitions\Pages\ListTaskDefinitions;
use App\Filament\Resources\TaskDefinitions\Pages\CreateTaskDefinition;
use App\Filament\Resources\TaskDefinitions\Schemas\TaskDefinitionForm;
use App\Filament\Resources\TaskDefinitions\Tables\TaskDefinitionsTable;

class TaskDefinitionResource extends Resource
{
    protected static ?string $model = TaskDefinition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Sparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Gamification';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Task Definition';

    public static function form(Schema $schema): Schema
    {
        return TaskDefinitionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaskDefinitionsTable::configure($table);
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
            'index' => ListTaskDefinitions::route('/'),
            'create' => CreateTaskDefinition::route('/create'),
            'edit' => EditTaskDefinition::route('/{record}/edit'),
        ];
    }
}
