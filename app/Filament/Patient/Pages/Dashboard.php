<?php

namespace App\Filament\Patient\Pages;

use App\Filament\Patient\Widgets\MyPharmacistWidget;
use App\Filament\Patient\Widgets\UpcomingRefillsWidget;
use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    public function getWidgets(): array
    {
        return [
            MyPharmacistWidget::class,
            UpcomingRefillsWidget::class,
        ];
    }
}
