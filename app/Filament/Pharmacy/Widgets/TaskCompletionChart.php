<?php

namespace App\Filament\Pharmacy\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Src\Gamification\Domain\Models\GamificationLedgerEntry;

class TaskCompletionChart extends ChartWidget
{
    protected ?string $heading = 'Completed Tasks by Type';

    protected static ?int $sort = 6;

    protected function getData(): array
    {
        $data = GamificationLedgerEntry::query()
            ->where('user_id', Auth::id())
            ->with('taskDefinition')
            ->get()
            ->groupBy('taskDefinition.name')
            ->map(fn ($group) => $group->count());

        return [
            'datasets' => [
                [
                    'label' => 'Tasks',
                    'data' => $data->values()->all(),
                    // Define an array of colors here.
                    // Chart.js will assign them in order to your data points.
                    'backgroundColor' => [
                        '#3b82f6', // Blue
                        '#ef4444', // Red
                        '#22c55e', // Green
                        '#eab308', // Yellow
                        '#a855f7', // Purple
                        '#f97316', // Orange
                        '#06b6d4', // Cyan
                        '#ec4899', // Pink
                    ],
                    'borderColor' => '#ffffff', // Optional: Adds a white border between segments
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $data->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
