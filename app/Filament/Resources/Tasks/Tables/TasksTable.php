<?php

namespace App\Filament\Resources\Tasks\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Src\Gamification\Domain\Models\Task;
use Src\Shared\Domain\Models\User;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('taskDefinition.name')->label('Task')->searchable()->wrap(),
                TextColumn::make('assignee.name')->label('Assigned To')->searchable(),
                TextColumn::make('subjects_description')
                    ->label('Subject(s)')
                    ->wrap(),

                SelectColumn::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'overdue' => 'Overdue',
                    ]),
                TextColumn::make('due_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'completed' => 'Completed']),
                SelectFilter::make('pharmacy')
                    ->relationship('assignee.pharmacy', 'name'),
                SelectFilter::make('assigned_to_user_id')->label('Assigned To')
                    ->searchable()
                    ->relationship('assignee', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('reassign')
                    ->label('Reassign')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('info')
                    ->schema([
                        Select::make('new_assignee_id')
                            ->label('New Assignee')
                            ->options(function (Task $record) {
                                // Only suggest users from the same pharmacy
                                if ($record->assignee?->pharmacy_id) {
                                    return User::where('pharmacy_id', $record->assignee->pharmacy_id)->pluck('name', 'id');
                                }

                                return User::where('is_pharmacist', true)->orWhere('is_technician', true)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Task $record, array $data) {
                        $newAssignee = User::find($data['new_assignee_id']);
                        if ($newAssignee) {
                            $oldAssigneeName = $record->assignee->name;
                            $record->update(['assigned_to_user_id' => $newAssignee->id]);

                            // Send Filament Notification
                            Notification::make()
                                ->title('Task Reassigned to You')
                                ->body("The task '{$record->taskDefinition->name}' was reassigned to you by an administrator.")
                                ->warning()
                                ->sendToDatabase($newAssignee);

                            // Send Telegram Notification
                            $telegramService = app(\Src\Shared\Infrastructure\Services\TelegramService::class);
                            $message = "🔄 *Task Reassigned*\n\n".
                                       "The task '{$record->taskDefinition->name}' (previously assigned to {$oldAssigneeName}) has now been assigned to you.";
                            $telegramService->sendMessageToUser($newAssignee, $message);

                            Notification::make()->title('Task Reassigned Successfully')->success()->send();
                        }
                    }),
                Action::make('view_subjects')
                    ->label('View Subjects')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn (Task $record) => view('filament.admin.modals.view-task-subjects', ['subjects' => $record->pharmacyProducts]))
                    ->modalSubmitAction(false) // This makes it a read-only modal
                    ->visible(fn (Task $record) => $record->pharmacyProducts()->exists()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
