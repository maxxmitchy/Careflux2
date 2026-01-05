<?php

namespace App\Filament\Pharmacy\Pages;

use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Src\Shared\Domain\Models\User;
use UnitEnum;

class TeamPerformanceReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pharmacy.pages.team-performance-report';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Team Performance';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    /** Pharmacists only */
    public ?Collection $pharmacistPerformanceData = null;

    /** Technicians only */
    public ?Collection $technicianPerformanceData = null;

    /** Month selector (YYYY-MM) */
    public ?string $selectedMonth = null;

    /** Week number within month (1–5) */
    public int $selectedWeek = 1;

    /** Exposed week range (for Blade) */
    public ?Carbon $startOfWeek = null;

    public ?Carbon $endOfWeek = null;

    /**
     * Navigation gate — only managers see this page.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return Filament::auth()->user()->is_manager;
    }

    /**
     * Route-level security + defaults
     */
    public function mount(): void
    {
        abort_unless(Filament::auth()->user()->is_manager, 403);

        $this->selectedMonth = now()->format('Y-m');

        // Determine which week of the month today falls in
        $today = now();
        $monthStart = $today->copy()->startOfMonth();

        $weekNumber = intval($monthStart->diffInWeeks($today)) + 1;

        $this->selectedWeek = $weekNumber;

        $this->loadReportData();
    }

    /**
     * Month picker form
     */
    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('selectedMonth')
                ->label('Select Month')
                ->displayFormat('F Y')
                ->format('Y-m')
                ->native(false)
                ->reactive(),
        ];
    }

    /**
     * Reset to Week 1 when month changes
     */
    public function updatedSelectedMonth(): void
    {
        $this->selectedWeek = 1;
        $this->loadReportData();
    }

    /**
     * Reload when week pill is clicked
     */
    public function updatedSelectedWeek(): void
    {
        $this->loadReportData();
    }

    /**
     * Calculate week range based on selected month + week
     */
    protected function resolveWeekRange(): void
    {
        $monthStart = Carbon::createFromFormat('Y-m', $this->selectedMonth)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $start = $monthStart
            ->copy()
            ->addWeeks($this->selectedWeek - 1)
            ->startOfWeek();

        $end = $start->copy()->endOfWeek();

        // Clamp to month boundaries
        $this->startOfWeek = $start->max($monthStart);
        $this->endOfWeek = $end->min($monthEnd);
    }

    /**
     * Core report query (week-scoped, pharmacists + technicians)
     */
    protected function loadReportData(): void
    {
        $manager = Filament::auth()->user();
        $pharmacyId = $manager->pharmacy_id;

        if (! $pharmacyId) {
            $this->pharmacistPerformanceData = collect();
            $this->technicianPerformanceData = collect();

            return;
        }

        // Resolve and expose week range
        $this->resolveWeekRange();

        $startOfWeek = $this->startOfWeek;
        $endOfWeek = $this->endOfWeek;

        $allStaff = User::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where(fn (Builder $query) => $query->where('is_pharmacist', true)
                ->orWhere('is_technician', true)
            )
            ->withCount([
                'tasks as completed_tasks_count' => fn (Builder $query) => $query->where('status', 'completed')
                    ->whereBetween('completed_at', [$startOfWeek, $endOfWeek]),

                'tasks as pending_tasks_count' => fn (Builder $query) => $query->where('status', 'pending'),

                'interactions as interactions_count' => fn (Builder $query) => $query->whereBetween('patient_interactions.created_at', [$startOfWeek, $endOfWeek]),
            ])
            ->withSum([
                'gamificationLedgerEntries as total_points_earned' => fn (Builder $query) => $query->whereBetween('gamification_ledger_entries.created_at', [$startOfWeek, $endOfWeek]),
            ], 'points_awarded')
            ->with([
                // Pharmacists only
                'pharmacistReports' => fn ($query) => $query->whereBetween('week_ending_date', [$startOfWeek, $endOfWeek])
                    ->latest(),
            ])
            ->get();

        [$pharmacists, $technicians] = $allStaff->partition(
            fn ($user) => $user->is_pharmacist
        );

        $this->pharmacistPerformanceData = $pharmacists->map(function ($user) {
            $user->report_for_week = $user->pharmacistReports->first();

            return $user;
        });

        $this->technicianPerformanceData = $technicians;
    }
}
