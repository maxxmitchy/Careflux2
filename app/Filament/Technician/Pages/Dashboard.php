<?php

namespace App\Filament\Technician\Pages;

use App\Filament\Technician\Widgets\OrdersToPackWidget;
use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    public function getWidgets(): array
    {
        return [
            OrdersToPackWidget::class,
        ];
    }
}
