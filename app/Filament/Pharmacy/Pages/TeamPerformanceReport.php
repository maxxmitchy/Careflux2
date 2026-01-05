<?php

namespace App\Filament\Pharmacy\Pages;

use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Src\Shared\Domain\Models\User;
use UnitEnum;

class TeamPerformanceReport extends Page
{
    protected string $view = 'filament.pharmacy.pages.team-performance-report';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Team Performance';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    public ?Collection $performanceData = null;

    /**
     * This method is the security gate. The navigation item will only appear for managers.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return Filament::auth()->user()->is_manager;
    }

    /**
     * This is the second security gate. It prevents non-managers from accessing the URL directly.
     */
    public function mount(): void
    {
        abort_unless(Filament::auth()->user()->is_manager, 403);
        $this->loadReportData();
    }

    protected function loadReportData(): void
    {
        $manager = Filament::auth()->user();
        $pharmacyId = $manager->pharmacy_id;

        if (! $pharmacyId) {
            $this->performanceData = collect();

            return;
        }

        // This is the single, high-performance query to get all data.
        $this->performanceData = User::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('is_pharmacist', true) // Only show pharmacists, not techs or other managers
            ->withCount([
                'tasks as completed_tasks_count' => fn ($query) => $query->where('status', 'completed'),
                'tasks as pending_tasks_count' => fn ($query) => $query->where('status', 'pending'),
                'interactions as interactions_count',
            ])
            ->withSum('gamificationLedgerEntries as total_points_earned', 'points_awarded')
            ->with('latestPharmacistReport') // We will add this relationship to the User model
            ->get();
    }
}
