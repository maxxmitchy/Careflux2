<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Src\Gamification\Application\Actions\AwardPointsAction;
use Src\Gamification\Domain\Models\Task;
use Src\Patient\Domain\Models\Patient;
use Src\Pharmacy\Domain\Models\ProductExpiry;
use Src\Shared\Domain\Models\User;

class TeamTasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // 1. GROUP BY USER: This organizes the view by staff member
            ->groups([
                'assignee.name',
                'status',
            ])
            ->defaultGroup('assignee.name')
            ->columns([
                TextColumn::make('taskDefinition.name')
                    ->label('Task')
                    ->weight(FontWeight::Bold)
                    ->description(fn (Task $record) => $record->taskDefinition->description)
                    ->searchable()
                    ->wrap(),

                // We don't need "Assigned To" column if we are grouping by it,
                // but keep it toggleable or visible if grouping is turned off.
                TextColumn::make('assignee.name')
                    ->label('Assigned To')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('subjects_description')
                    ->label('Subject(s)')
                    ->limit(50)
                    ->wrap(),

                TextColumn::make('due_at')
                    ->date('M j, Y') // Cleaner date format
                    ->description(fn (Task $record) => $record->due_at->diffForHumans())
                    ->sortable()
                    ->color(fn ($record) => $record->isOverdue() ? 'danger' : 'gray'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'overdue' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('assigned_to_user_id')
                    ->label('Staff Member')
                    ->relationship('assignee', 'name'), // Cleaner filter definition
                SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'completed' => 'Completed']),
            ])
            ->recordActions([
                // 2. REPLICATE ACTION: Maximum efficiency for the manager
                // Allows cloning a task to assign the same job to someone else quickly
                ReplicateAction::make()
                    ->label('Clone')
                    ->excludeAttributes(['status', 'completed_at', 'results'])
                    ->modalHeading('Duplicate Task')
                    ->schema([
                        // Allow changing the assignee immediately when cloning
                        \Filament\Forms\Components\Select::make('assigned_to_user_id')
                            ->relationship('assignee', 'name')
                            ->label('Assign Clone To')
                            ->required(),
                        \Filament\Forms\Components\DatePicker::make('due_at')
                            ->default(now())
                            ->required(),
                    ])
                    ->beforeReplicaSaved(function (Task $replica, array $data) {
                        $replica->assigned_to_user_id = $data['assigned_to_user_id'];
                        $replica->due_at = $data['due_at'];
                        $replica->status = 'pending';
                    }),

                EditAction::make(),
                Action::make('complete_task')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (Task $record): bool => $record->status === 'pending')
                    ->schema(function (Task $record): array {
                        $taskKey = $record->taskDefinition->key;
                        if (str_starts_with($taskKey, 'PATIENT_FOLLOW_UP')) {
                            return [Textarea::make('notes')->required()->label('Follow-up Notes')];
                        }
                        if ($taskKey === 'TECHNICIAN_EXPIRY_LOG') {
                            $products = $record->pharmacyProducts()->with('medicationVariant.medication')->get();

                            return [
                                Repeater::make('products')->schema([
                                    Hidden::make('id'),
                                    TextEntry::make('name'),
                                    DatePicker::make('expiry_date')->required()->native(false),
                                    TextInput::make('quantity')->numeric()->integer()->required()->minValue(1),
                                ])->columns(3)->addable(false)->deletable(false)
                                    ->default($products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])->all()),
                            ];
                        }

                        return [];
                    })
                    ->action(function (Task $record, array $data, AwardPointsAction $awardPoints) {
                        $taskKey = $record->taskDefinition->key;

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

                        if ($taskKey === 'TECHNICIAN_EXPIRY_LOG') {
                            DB::transaction(function () use ($data) {
                                foreach ($data['products'] as $productData) {
                                    ProductExpiry::create([
                                        'pharmacy_product_id' => $productData['id'],
                                        'expiry_date' => $productData['expiry_date'],
                                        'quantity' => $productData['quantity'],
                                        'logged_by_user_id' => Auth::id(),
                                    ]);
                                }
                            });
                        }
                        $record->update(['status' => 'completed', 'completed_at' => now()]);
                        $awardPoints->execute(Auth::user(), $taskKey, $record);
                        Notification::make()->title('Task Completed & Points Awarded!')->success()->send();
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
