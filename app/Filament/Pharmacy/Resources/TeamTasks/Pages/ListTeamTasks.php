<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Pages;

use App\Filament\Pharmacy\Resources\TeamTasks\TeamTaskResource;
use App\Filament\Pharmacy\Resources\TeamTasks\Widgets\TaskPerformanceStats;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTeamTasks extends ListRecords
{
    protected static string $resource = TeamTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Assign New Task'),
        ];
    }

    // 1. Add Stats Widget at the top of the list
    protected function getHeaderWidgets(): array
    {
        return [
            TaskPerformanceStats::class,
        ];
    }

    // 2. Add Tabs for quick filtering
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Tasks'),

            'today' => Tab::make('Due Today')
                ->icon('heroicon-m-calendar')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('due_at', today()))
                ->badge(fn () => $this->getModel()::whereDate('due_at', today())->where('status', 'pending')->count())
                ->badgeColor('warning'),

            'overdue' => Tab::make('Overdue')
                ->icon('heroicon-m-exclamation-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('due_at', '<', now())->where('status', 'pending'))
                ->badge(fn () => $this->getModel()::where('due_at', '<', now())->where('status', 'pending')->count())
                ->badgeColor('danger'),

            'completed' => Tab::make('Completed')
                ->icon('heroicon-m-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed')),
        ];
    }
}
