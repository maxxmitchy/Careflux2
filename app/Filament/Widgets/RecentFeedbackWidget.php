<?php

namespace App\Filament\Widgets;

use App\Models\PharmacistReport;
use Filament\Widgets\Widget;

class RecentFeedbackWidget extends Widget
{
    protected string $view = 'filament.widgets.recent-feedback-widget';

    protected int|string|array $columnSpan = 'full';

    public \Illuminate\Database\Eloquent\Collection $latestReports;

    public function mount(): void
    {
        $this->latestReports = PharmacistReport::with('user.pharmacy')->latest()->limit(3)->get();
    }
}
