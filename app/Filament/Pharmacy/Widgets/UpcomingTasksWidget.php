<?php

namespace App\Filament\Pharmacy\Widgets;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Src\Gamification\Application\Actions\AwardPointsAction;
use Src\Patient\Domain\Models\Prescription;

class UpcomingTasksWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Actionable Tasks: Upcoming Refills';

    public function table(Table $table): Table
    {
        $user = Filament::auth()->user();

        return $table
            ->query(
                Prescription::query()
                    ->whereHas('patient', fn (Builder $q) => $q->where('pharmacist_id', $user->id))
                    ->where('is_recurring', true)
                    ->whereDate('refill_due_date', '>=', now())
                    ->whereDate('refill_due_date', '<=', now()->addDays(7))
            )
            ->defaultSort('refill_due_date', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('patient.full_name')->searchable(),
                Tables\Columns\TextColumn::make('medication.name'),
                Tables\Columns\TextColumn::make('refill_due_date')->date()->sortable(),
            ])
            ->recordActions([
                Action::make('log_follow_up')
                    ->label('Log Follow-up')
                    ->icon('heroicon-o-phone-arrow-up-right')
                    ->schema([
                        Select::make('type')
                            ->options([
                                'Refill Reminder Call' => 'Refill Reminder Call',
                                'Refill Reminder WhatsApp' => 'Refill Reminder WhatsApp',
                            ])->required(),
                        Textarea::make('notes')->required(),
                    ])
                    ->action(function (array $data, Prescription $record, AwardPointsAction $awardPoints) {
                        $interaction = $record->patient->interactions()->create([
                            'user_id' => Filament::auth()->id(),
                            'type' => $data['type'],
                            'notes' => $data['notes'],
                        ]);

                        // Award points for completing the task
                        $awardPoints->execute(Filament::auth()->user(), 'PATIENT_FOLLOW_UP_REFILL', $interaction);

                        Notification::make()->title('Follow-up logged and points awarded!')->success()->send();

                        // Refresh widgets to show updated stats
                        $this->dispatch('update-stats');
                    }),
            ]);
    }

    // Listen for the event to refresh the widgets
    protected function getListeners(): array
    {
        return [
            'update-stats' => '$refresh',
        ];
    }
}
