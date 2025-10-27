<?php

namespace App\Filament\Pharmacy\Widgets;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Src\Gamification\Domain\Models\GamificationLedgerEntry;

class PointsEarnedChart extends ChartWidget
{
    protected ?string $heading = 'Points Earned (Last 30 Days)';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $data = GamificationLedgerEntry::query()
            ->where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(points_awarded) as total_points'),
            ])
            ->pluck('total_points', 'date');

        // Fill in missing days with 0 points for a continuous line
        $period = CarbonPeriod::create(now()->subDays(29), now());
        $labels = collect($period)->map(fn ($date) => $date->format('M d'));
        $dataset = $labels->map(fn ($label) => $data[Carbon::parse($label)->format('Y-m-d')] ?? 0);

        return [
            'datasets' => [['label' => 'Points Earned', 'data' => $dataset->all(), 'borderColor' => '#10B981']],
            'labels' => $labels->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
