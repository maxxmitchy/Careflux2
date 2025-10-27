<?php

namespace App\Filament\Widgets\Charts;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Src\Patient\Domain\Models\Patient;
use Src\Shared\Domain\Models\User;

class UserGrowthChart extends ChartWidget
{
    protected ?string $heading = 'User Growth (This Year)';

    protected function getData(): array
    {
        $patients = Patient::query()
            ->whereYear('created_at', now()->year)
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('count(*) as count'))
            ->groupBy('month')->orderBy('month')->pluck('count', 'month');

        $pharmacists = User::query()
            ->where('is_pharmacist', true)
            ->whereYear('created_at', now()->year)
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('count(*) as count'))
            ->groupBy('month')->orderBy('month')->pluck('count', 'month');

        $months = collect(range(1, 12))->map(fn ($month) => now()->month($month)->format('Y-m'));

        return [
            'datasets' => [
                ['label' => 'New Patients', 'data' => $months->map(fn ($m) => $patients->get($m, 0)), 'backgroundColor' => '#3B82F6'],
                ['label' => 'New Pharmacists', 'data' => $months->map(fn ($m) => $pharmacists->get($m, 0)), 'backgroundColor' => '#10B981'],
            ],
            'labels' => $months->map(fn ($m) => Carbon::createFromFormat('Y-m', $m)->format('M')),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
