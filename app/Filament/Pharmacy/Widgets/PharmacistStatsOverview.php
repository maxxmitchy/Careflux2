<?php

namespace App\Filament\Pharmacy\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Src\Patient\Domain\Models\Prescription;

class PharmacistStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Filament::auth()->user();

        $pendingRefills = Prescription::query()
            ->whereHas('patient', fn ($q) => $q->where('pharmacist_id', $user->id))
            ->where('is_recurring', true)
            ->whereDate('refill_due_date', '>=', now())
            ->whereDate('refill_due_date', '<=', now()->addDays(7))
            ->count();

        return [
            Stat::make('Active Patients', $user->assignedPatients()->count())
                ->description('Total patients under your care')
                ->color('success'),
            Stat::make('Pending Tasks', $pendingRefills)
                ->description('Upcoming refills in next 7 days')
                ->color('warning'),
            Stat::make('Current Level', $user->level)
                ->description($user->points_balance.' Points')
                ->color('info'),
        ];
    }
}
