<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Src\Gamification\Application\Actions\AwardPointsAction;
use Src\Gamification\Domain\Models\Task;
use Src\Patient\Domain\Models\Patient;
use Src\Shared\Domain\Models\User;

class TeamTasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('taskDefinition.name')->label('Task')->searchable(),
                TextColumn::make('assignedTo.name')->label('Assigned To')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('due_at')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('assigned_to_user_id')
                    ->label('Filter by Staff')
                    ->options(fn () => User::where('pharmacy_id', Auth::user()->pharmacy_id)->pluck('name', 'id')),
                SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'completed' => 'Completed']),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('complete_task')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Task $record): bool => $record->status === 'pending')
                    ->schema(function (Task $record) {
                        if (str_starts_with($record->taskDefinition->key, 'PATIENT_FOLLOW_UP')) {
                            return [
                                Textarea::make('notes')->required()->label('Follow-up Notes'),
                            ];
                        }
                        if ($record->taskDefinition->key === 'TECHNICIAN_PRICE_VERIFY') {
                            return [
                                TextInput::make('new_price')->numeric()->required()->prefix('₦'),
                            ];
                        }

                        return [];
                    })
                    ->action(function (Task $record, array $data, AwardPointsAction $awardPoints) {
                        if (str_starts_with($record->taskDefinition->key, 'PATIENT_FOLLOW_UP')) {
                            if (! $record->subjectable instanceof Patient) {
                                return;
                            }
                            $record->subjectable->interactions()->create([
                                'user_id' => Auth::id(),
                                'type' => $record->taskDefinition->name,
                                'notes' => $data['notes'],
                            ]);
                        }

                        $record->update(['status' => 'completed', 'completed_at' => now()]);
                        $awardPoints->execute(Auth::user(), $record->taskDefinition->key, $record);

                        Notification::make()
                            ->title('Task Completed & Points Awarded!')
                            ->body("{$record->assignee->name} has been awarded {$record->taskDefinition->points} points.")
                            ->success()
                            ->send();
                    })
                    ->modalHeading(fn (Task $record) => 'Complete Task: '.$record->taskDefinition->name)
                    ->modalWidth('lg'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
