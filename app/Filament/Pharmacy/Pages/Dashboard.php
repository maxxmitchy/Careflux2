<?php

namespace App\Filament\Pharmacy\Pages;

use App\Filament\Pharmacy\Widgets\GamificationWidget;
use App\Filament\Pharmacy\Widgets\PharmacistStatsOverview;
use App\Filament\Pharmacy\Widgets\UpcomingTasksWidget;
use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    public function getWidgets(): array
    {
        return [
            PharmacistStatsOverview::class,
            GamificationWidget::class,
            UpcomingTasksWidget::class,
        ];
    }
}
