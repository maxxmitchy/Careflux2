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
            'datasets' => [['label' => 'Tasks', 'data' => $data->values()->all()]],
            'labels' => $data->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
