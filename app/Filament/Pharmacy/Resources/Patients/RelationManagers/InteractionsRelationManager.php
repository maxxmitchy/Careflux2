<?php

namespace App\Filament\Pharmacy\Resources\Patients\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Src\Gamification\Application\Actions\AwardPointsAction;
use Src\Gamification\Domain\Models\TaskDefinition;
use Src\Patient\Domain\Models\PatientInteraction;

class InteractionsRelationManager extends RelationManager
{
    protected static string $relationship = 'interactions';

    protected static ?string $title = 'Follow-up & Interaction Log';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->required()
                    ->options([
                        'PATIENT_FOLLOW_UP_48HR' => '48-Hour Check-in',
                        'PATIENT_FOLLOW_UP_7DAY' => '7-Day Follow-up',
                        'PATIENT_FOLLOW_UP_REFILL' => 'Refill Reminder Call',
                        'GENERAL_NOTE' => 'General Note / Consultation',
                    ])
                    ->helperText('Select the primary purpose of this interaction.'),
                Textarea::make('notes')
                    ->required()
                    ->rows(4)
                    ->helperText('Summarize the conversation and any outcomes or actions needed.')
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
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')->label('Interaction Type')
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', Str::title($state))),
                TextColumn::make('notes')->limit(50)->wrap(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // AssociateAction::make(),

                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        // Automatically assign the logged-in pharmacist
                        $data['user_id'] = Auth::id();

                        return $data;
                    })
                    ->after(function (PatientInteraction $record, AwardPointsAction $awardPoints) {
                        // GAMIFICATION HOOK: Award points after a successful log.
                        // We will need to map the 'type' to a 'task_key'.
                        $taskKey = $record->type; // The 'type' from the form IS the task key

                        $taskDefinition = TaskDefinition::where('key', $taskKey)->first();

                        if (! $taskDefinition) {
                            Notification::make()
                                ->title('Interaction Logged')
                                ->body('Note: No points were awarded as this interaction type does not have a defined task.')
                                ->info()
                                ->send();

                            return;
                        }

                        $awardPoints->execute(
                            user: $record->user,
                            taskKey: $taskKey,
                            subjectable: $record
                        );

                        Notification::make()
                            ->title('Follow-up Logged & Points Awarded!')
                            ->body("You've earned {$taskDefinition->points} points for this interaction.")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                // ViewAction::make(),
                EditAction::make(),
                // DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
