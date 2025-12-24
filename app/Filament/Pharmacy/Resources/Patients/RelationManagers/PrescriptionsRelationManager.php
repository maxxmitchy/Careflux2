<?php

namespace App\Filament\Pharmacy\Resources\Patients\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Src\Audit\Domain\Models\PharmacistActionLog;
use Src\Medication\Domain\Models\Medication;
use Src\Patient\Domain\Models\Prescription;

class PrescriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'prescriptions';

    protected static ?string $title = 'Patient Prescriptions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('medication_id')
                    ->label('Medication')
                    ->relationship('medication', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Search the global catalog. If a medication is not listed, you can create it here.')
                    ->createOptionForm([
                        // Form for creating a new global medication on the fly
                        TextInput::make('name')->required()->unique(),
                        TextInput::make('generic_name'),
                        Toggle::make('is_prescription')->default(true),
                    ])
                    ->createOptionUsing(fn (array $data) => Medication::create($data)->id)
                    ->live(), // Make the form reactive to changes

                TextInput::make('dosage')
                    ->required()
                    ->helperText('Enter the specific dosage and form, e.g., "500mg tablet", "10ml suspension".'),

                TextInput::make('days_supply')
                    ->label('Days of Supply')
                    ->numeric()
                    ->helperText('e.g., 30, 60, 90. This will auto-calculate the next refill date.')
                    ->live(onBlur: true) // Update when user clicks away
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if (blank($state)) {
                            $set('refill_due_date', null);

                            return;

                        }
                        // Automatically calculate the next refill date
                        $set('refill_due_date', now()->addDays((int) $state)->format('Y-m-d'));
                    }),

                DatePicker::make('refill_due_date')->native(false)->helperText('The date the patient is expected to need their next supply.'),

                Toggle::make('is_recurring')->inline(false)
                    ->helperText('Enable this for medications the patient takes long-term.'),

                Textarea::make('notes')
                    ->label('Pharmacist Notes')
                    ->helperText('(Optional) Add any clinical notes or instructions for this prescription.')
                    ->columnSpanFull(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('patient_id'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('patient_id')
            ->columns([
                TextColumn::make('medication.name'),
                TextColumn::make('dosage'),
                IconColumn::make('is_recurring')->boolean(),
                TextColumn::make('refill_due_date')->date()->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),

            ])
            ->recordActions([
                // ViewAction::make(),
                EditAction::make(),
                // DissociateAction::make(),
                DeleteAction::make(),

                Action::make('dispense_refill')
                    ->label('Dispense & Reschedule')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Refill Dispensation')
                    ->modalDescription('This will log the refill and automatically schedule the next refill date based on the days supply.')
                    ->action(function (Prescription $record) {
                        if (! $record->days_supply) {
                            Notification::make()
                                ->title('Action Failed')
                                ->body('Cannot reschedule refill because "Days Supply" is not set for this prescription.')
                                ->danger()->send();

                            return;
                        }

                        $oldDate = $record->refill_due_date?->format('M d, Y') ?? 'N/A';
                        $newDate = now()->addDays($record->days_supply);

                        $record->update(['refill_due_date' => $newDate]);

                        PharmacistActionLog::create([
                            'user_id' => Auth::id(),
                            'subjectable_id' => $record->id,
                            'subjectable_type' => $record->getMorphClass(),
                            'action_type' => 'REFILL_DISPENSED_AND_RESCHEDULED',
                            'description' => "Dispensed refill for {$record->medication->name} and rescheduled next refill.",
                            'metadata' => [
                                'patient_id' => $record->patient_id,
                                'medication_id' => $record->medication_id,
                                'days_supply' => $record->days_supply,
                                'previous_refill_date' => $oldDate,
                                'new_refill_date' => $newDate->format('M d, Y'),
                            ],
                        ]);

                        Notification::make()
                            ->title('Refill Rescheduled Successfully')
                            ->body("The next refill for {$record->medication->name} has been set to {$newDate->format('M d, Y')}.")
                            ->success()->send();
                    })
                    ->visible(fn (Prescription $record): bool => $record->is_recurring),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
