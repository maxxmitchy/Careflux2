<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Src\Gamification\Domain\Models\Task;

class TaskPerformanceStats extends BaseWidget
{
    protected function getStats(): array
    {
        $pharmacyId = Auth::user()->pharmacy_id;

        // Calculate daily stats
        $todayTasks = Task::query()
            ->whereHas('assignedTo', fn ($q) => $q->where('pharmacy_id', $pharmacyId))
            ->whereDate('due_at', today());

        $totalToday = (clone $todayTasks)->count();
        $completedToday = (clone $todayTasks)->where('status', 'completed')->count();
        $pendingToday = $totalToday - $completedToday;

        // Completion Rate
        $rate = $totalToday > 0 ? round(($completedToday / $totalToday) * 100) : 0;

        // Find top performer (User with most completed tasks this week)
        $topPerformer = Task::query()
            ->selectRaw('assigned_to_user_id, count(*) as count')
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->startOfWeek())
            ->groupBy('assigned_to_user_id')
            ->orderByDesc('count')
            ->with('assignedTo')
            ->first();

        $topPerformerName = $topPerformer ? $topPerformer->assignedTo->name : 'N/A';

        return [
            Stat::make('Completion Rate (Today)', $rate.'%')
                ->description($pendingToday.' tasks remaining')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($rate > 80 ? 'success' : 'warning'),

            Stat::make('Top Performer (Week)', $topPerformerName)
                ->description('Most tasks completed')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('primary'),

            Stat::make('Overdue Tasks', Task::where('status', 'pending')->where('due_at', '<', now())->count())
                ->description('Requires immediate attention')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color('danger'),
        ];
    }
}
