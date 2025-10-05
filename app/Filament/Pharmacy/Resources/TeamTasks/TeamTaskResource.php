<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks;

use App\Filament\Pharmacy\Resources\TeamTasks\Pages\CreateTeamTask;
use App\Filament\Pharmacy\Resources\TeamTasks\Pages\EditTeamTask;
use App\Filament\Pharmacy\Resources\TeamTasks\Pages\ListTeamTasks;
use App\Filament\Pharmacy\Resources\TeamTasks\Pages\ViewTeamTask;
use App\Filament\Pharmacy\Resources\TeamTasks\Schemas\TeamTaskForm;
use App\Filament\Pharmacy\Resources\TeamTasks\Schemas\TeamTaskInfolist;
use App\Filament\Pharmacy\Resources\TeamTasks\Tables\TeamTasksTable;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Gamification\Domain\Models\Task;
use Src\Shared\Domain\Models\User;
use UnitEnum;

class TeamTaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    protected static ?string $modelLabel = 'Team Task';

    protected static ?string $pluralModelLabel = 'Team Tasks';

    protected static string|UnitEnum|null $navigationGroup = 'Team Management';

    public static function form(Schema $schema): Schema
    {
        return TeamTaskForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TeamTaskInfolist::configure($schema);
    }

    /**
     * This is the master security gate. The entire resource will only appear
     * in the navigation if the logged-in user is a manager.
     */
    public static function canViewAny(): bool
    {
        return Filament::auth()->user()->is_manager;
    }

    public static function table(Table $table): Table
    {
        return TeamTasksTable::configure($table);
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
            'index' => ListTeamTasks::route('/'),
            'create' => CreateTeamTask::route('/create'),
            'view' => ViewTeamTask::route('/{record}'),
            'edit' => EditTeamTask::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();
        $pharmacyUserIds = User::where('pharmacy_id', $user->pharmacy_id)->pluck('id');

        return parent::getEloquentQuery()
            ->with(['assignedTo', 'taskDefinition']) // <-- EAGER LOAD RELATIONSHIPS
            ->whereIn('assigned_to_user_id', $pharmacyUserIds);
    }
}
