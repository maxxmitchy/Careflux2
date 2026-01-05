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

    public ?Collection $performanceData = null;

    /** Selected date used to determine the week */
    public ?string $selectedDate = null;

    /**
     * Navigation gate — only managers see this page.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return Filament::auth()->user()->is_manager;
    }

    /**
     * Route-level security + default date
     */
    public function mount(): void
    {
        abort_unless(Filament::auth()->user()->is_manager, 403);

        $this->form->fill([
            'selectedDate' => now()->format('Y-m-d'),
        ]);

        $this->loadReportData();
    }

    /**
     * Date filter form
     */
    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('selectedDate')
                ->label('Select a Week')
                ->native(false)
                ->closeOnDateSelection()
                ->default(now())
                ->reactive(),
        ];
    }

    /**
     * Auto-refresh report when date changes
     */
    public function updatedSelectedDate(): void
    {
        $this->loadReportData();
    }

    /**
     * Core report query (week-scoped)
     */
    protected function loadReportData(): void
    {
        $manager = Filament::auth()->user();
        $pharmacyId = $manager->pharmacy_id;

        if (! $pharmacyId) {
            $this->performanceData = collect();
            return;
        }

        $date = Carbon::parse($this->selectedDate);
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek   = $date->copy()->endOfWeek();

        $this->performanceData = User::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('is_pharmacist', true)
            ->withCount([
                'tasks as completed_tasks_count' => fn (Builder $query) =>
                    $query->where('status', 'completed')
                          ->whereBetween('completed_at', [$startOfWeek, $endOfWeek]),

                'tasks as pending_tasks_count' => fn (Builder $query) =>
                    $query->where('status', 'pending'),

                'interactions as interactions_count' => fn (Builder $query) => $query->whereBetween('patient_interactions.created_at', [$startOfWeek, $endOfWeek]),
            ])
            ->withSum([
                'gamificationLedgerEntries as total_points_earned' => fn (Builder $query) =>
                    $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]),
            ], 'points_awarded')
            ->with([
                'pharmacistReports' => function ($query) use ($startOfWeek, $endOfWeek) {
                    $query->whereBetween('week_ending_date', [$startOfWeek, $endOfWeek])
                          ->latest();
                },
            ])
            ->get()
            ->map(function ($user) {
                // Attach a single report for easy Blade access
                $user->report_for_week = $user->pharmacistReports->first();
                return $user;
            });
    }
}
