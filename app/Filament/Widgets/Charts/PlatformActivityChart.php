<?php

namespace App\Filament\Widgets\Charts;

use App\Models\PharmacistReport;
use Filament\Widgets\ChartWidget;
use Src\Gamification\Domain\Models\Task;
use Src\Order\Domain\Models\Invoice;

class PlatformActivityChart extends ChartWidget
{
    protected ?string $heading = 'Platform Activity';

    protected function getData(): array
    {
        return [
            'datasets' => [[
                'label' => 'Activities',
                'data' => [
                    Invoice::where('status', 'delivered')->count(),
                    Task::where('status', 'completed')->count(),
                    PharmacistReport::count(),
                ],
                'backgroundColor' => ['#10B981', '#3B82F6', '#F59E0B'],
            ]],
            'labels' => ['Completed Orders', 'Completed Tasks', 'Pharmacist Reports'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
