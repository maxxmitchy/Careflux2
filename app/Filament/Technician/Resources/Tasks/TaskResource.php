<?php

namespace App\Filament\Technician\Resources\Tasks;

use App\Filament\Technician\Resources\Tasks\Pages\CreateTask;
use App\Filament\Technician\Resources\Tasks\Pages\EditTask;
use App\Filament\Technician\Resources\Tasks\Pages\ListTasks;
use App\Filament\Technician\Resources\Tasks\Pages\ViewTask;
use App\Filament\Technician\Resources\Tasks\Schemas\TaskForm;
use App\Filament\Technician\Resources\Tasks\Schemas\TaskInfolist;
use App\Filament\Technician\Resources\Tasks\Tables\TasksTable;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Src\Gamification\Domain\Models\Task;
use Src\Shared\Domain\Models\User;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'status';

    protected static ?string $modelLabel = 'My Tasks';

    protected static ?int $navigationSort = 2;

    /**
     * This resource is only visible to managers and technicians.
     */
    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();

        return $user->is_manager || $user->is_technician;
    }

    public static function canCreate(): bool
    {
        return Filament::auth()->user()->is_manager;
    }

    public static function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TaskInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TasksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * This is the core scoping logic.
     */
    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();
        $query = parent::getEloquentQuery();

        if ($user->is_manager) {

            $pharmacyUserIds = User::where('pharmacy_id', $user->pharmacy_id)->pluck('id');

            return $query->whereIn('assigned_to_user_id', $pharmacyUserIds);
        }

        return $query->where('assigned_to_user_id', $user->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTasks::route('/'),
            'create' => CreateTask::route('/create'),
            'view' => ViewTask::route('/{record}'),
            'edit' => EditTask::route('/{record}/edit'),
        ];
    }

    public static function create(array $data): Task
    {
        $data['created_by_user_id'] = Auth::id();

        return static::getModel()::create($data);
    }
}
