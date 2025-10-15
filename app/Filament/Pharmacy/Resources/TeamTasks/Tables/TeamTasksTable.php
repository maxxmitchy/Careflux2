<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
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
            ->columns([
                TextColumn::make('taskDefinition.name')->label('Task')->searchable()->wrap(),
                TextColumn::make('assignee.name')->label('Assigned To')->searchable(), // <-- CORRECT RELATIONSHIP NAME
                TextColumn::make('subjects_description')->label('Subject(s)')->wrap(),
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
