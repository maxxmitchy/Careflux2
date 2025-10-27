<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Src\Patient\Domain\Models\Patient;
use Src\Shared\Domain\Models\User;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Patients', Patient::count())
                ->description('All onboarded patients')
                ->color('success'),
            Stat::make('Pending Pharmacist Verifications', User::where('is_pharmacist', true)->whereNull('verified_at')->count())
                ->description('Pharmacists awaiting approval')
                ->color('warning'),
            Stat::make('Total Revenue (This Month)', '₦'.number_format(\Src\Order\Domain\Models\Transaction::where('status', 'completed')->where('created_at', '>=', now()->startOfMonth())->sum('amount') / 100))
                ->description('From all successful transactions')
                ->color('primary'),
        ];
    }
}
