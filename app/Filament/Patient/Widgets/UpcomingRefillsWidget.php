<?php

namespace App\Filament\Patient\Widgets;

use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Src\Patient\Domain\Models\Prescription;

class UpcomingRefillsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Upcoming Medication Refills';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Prescription::query()
                    ->where('patient_id', Filament::auth()->user()->patientProfile?->id)
                    ->where('is_recurring', true)
                    ->whereDate('refill_due_date', '>=', now())
                    ->whereDate('refill_due_date', '<=', now()->addDays(14)) // Next 2 weeks
            )
            ->columns([
                Tables\Columns\TextColumn::make('medication.name'),
                Tables\Columns\TextColumn::make('refill_due_date')->date()->sortable(),
            ])
            ->emptyStateHeading('No upcoming refills')
            ->paginated(false);
    }
}
