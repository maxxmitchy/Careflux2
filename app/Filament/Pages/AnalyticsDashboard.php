<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Charts\MonthlyRevenueChart;
use App\Filament\Widgets\Charts\PlatformActivityChart;
use App\Filament\Widgets\Charts\UserGrowthChart;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class AnalyticsDashboard extends Page
{
    protected string $view = 'filament.pages.analytics-dashboard';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -1; // Place at the top of the group

    /**
     * This method defines which widgets will be loaded onto the page.
     */
    protected function getHeaderWidgets(): array
    {
        return [
            MonthlyRevenueChart::class,
            UserGrowthChart::class,
            PlatformActivityChart::class,
        ];
    }
}
