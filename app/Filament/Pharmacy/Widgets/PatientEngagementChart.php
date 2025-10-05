<?php

namespace App\Filament\Pharmacy\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Src\Patient\Domain\Models\Patient;
use Src\Patient\Domain\Models\PatientInteraction;

class PatientEngagementChart extends ChartWidget
{
    protected ?string $heading = 'Weekly Patient Engagement';

    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $user = Auth::user();
        $endDate = now()->endOfWeek();
        $startDate = now()->subWeeks(3)->startOfWeek();

        $newPatients = Patient::query()
            ->where('pharmacist_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('WEEK(created_at, 1) as week, count(*) as count')
            ->groupBy('week')->pluck('count', 'week');

        $followUps = PatientInteraction::query()
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('WEEK(created_at, 1) as week, count(*) as count')
            ->groupBy('week')->pluck('count', 'week');

        $labels = [];
        for ($i = 3; $i >= 0; $i--) {
            $labels[now()->subWeeks($i)->weekOfYear] = now()->subWeeks($i)->startOfWeek()->format('M d');
        }

        return [
            'datasets' => [
                ['label' => 'New Patients Assigned', 'data' => array_values(array_replace(array_fill_keys(array_keys($labels), 0), $newPatients->all())), 'backgroundColor' => '#3B82F6'],
                ['label' => 'Follow-ups Logged', 'data' => array_values(array_replace(array_fill_keys(array_keys($labels), 0), $followUps->all())), 'backgroundColor' => '#10B981'],
            ],
            'labels' => array_values($labels),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
