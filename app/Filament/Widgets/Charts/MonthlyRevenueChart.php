<?php

namespace App\Filament\Widgets\Charts;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Src\Order\Domain\Models\Transaction;

class MonthlyRevenueChart extends ChartWidget
{
    protected ?string $heading = 'Monthly Revenue (NGN)';

    protected function getData(): array
    {
        $data = Transaction::query()
            ->where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->select(
                DB::raw('SUM(amount / 100) as total_revenue'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month")
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total_revenue', 'month');

        $labels = $data->keys()->map(fn ($month) => Carbon::createFromFormat('Y-m', $month)->format('M Y'));
        $dataset = $data->values();

        return [
            'datasets' => [['label' => 'Revenue', 'data' => $dataset, 'borderColor' => '#10B981']],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
