<?php

namespace App\Filament\Technician\Widgets;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Src\Gamification\Application\Actions\AwardPointsAction;
use Src\Gamification\Domain\Models\Task;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Pharmacy\Domain\Models\PriceHistory;

class MyTasksWidget extends BaseWidget
{
    protected static ?int $sort = 0; // Make this the top widget

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'My Pending Tasks';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->where('assigned_to_user_id', Filament::auth()->id())
                    ->where('status', 'pending')
                    ->with(['taskDefinition', 'subjectable'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('taskDefinition.name')->label('Task'),
                Tables\Columns\TextColumn::make('subjectable_text')->label('Subject')
                    ->default(function (Task $record): string {
                        if ($record->subjectable instanceof PharmacyProduct) {
                            return 'Product: '.$record->subjectable->name;
                        }

                        return 'System Task';
                    }),
                Tables\Columns\TextColumn::make('due_at')->label('Due')->since()->sortable(),
            ])
            ->recordActions([
                // --- DYNAMIC ACTION: UPDATE PRICE ---
                Action::make('update_price')
                    ->label('Update Price')
                    ->icon('heroicon-o-currency-naira')
                    ->schema([
                        TextInput::make('new_price')
                            ->label('New Price (in Naira)')
                            ->numeric()->required()->prefix('₦'),
                    ])
                    ->action(function (Task $record, array $data, AwardPointsAction $awardPoints) {
                        /** @var PharmacyProduct $product */
                        $product = $record->subjectable;
                        $newPriceInKobo = (int) ($data['new_price'] * 100);

                        // Update the product price
                        $product->update(['price' => $newPriceInKobo]);

                        // Log the price history
                        PriceHistory::create([
                            'pharmacy_product_id' => $product->id,
                            'price' => $newPriceInKobo,
                            'updated_by_user_id' => Auth::id(),
                        ]);

                        // Mark task as complete and award points
                        $record->update(['completed_at' => now(), 'status' => 'completed']);
                        $awardPoints->execute(Auth::user(), $record->taskDefinition->key, $record);

                        Notification::make()->title('Price updated and task completed!')->success()->send();
                    })
                    // This action is ONLY visible for "Verify Price" tasks
                    ->visible(fn (Task $record) => $record->taskDefinition->key === 'TECHNICIAN_PRICE_VERIFY'),

                Action::make('complete_task')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    // This action is only visible for pending tasks
                    ->visible(fn (Task $record): bool => $record->status === 'pending')
                    ->schema(function (Task $record) {
                        // Dynamically generate the form based on the task type
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

                        // Default form is just a confirmation
                        return [];
                    })
                    ->action(function (Task $record, array $data, AwardPointsAction $awardPoints) {
                        // --- Core Action Logic ---

                        // 1. Perform the task's specific action (e.g., log interaction)
                        if (str_starts_with($record->taskDefinition->key, 'PATIENT_FOLLOW_UP')) {
                            $record->subjectable->interactions()->create([
                                'user_id' => Auth::id(),
                                'type' => $record->taskDefinition->name,
                                'notes' => $data['notes'],
                            ]);
                        }
                        // Add more `if` blocks here for other task types...

                        // 2. Mark the task as complete
                        $record->update(['status' => 'completed', 'completed_at' => now()]);

                        // 3. Award points
                        $awardPoints->execute(Auth::user(), $record->taskDefinition->key, $record);

                        // 4. Send success notification
                        Notification::make()
                            ->title('Task Completed!')
                            ->body("You have been awarded {$record->taskDefinition->points} points.")
                            ->success()
                            ->send();

                        // 5. Refresh the component to remove the task from the list
                        $this->dispatch('update-stats');
                    })
                    ->modalHeading(fn (Task $record) => 'Complete Task: '.$record->taskDefinition->name)
                    ->modalWidth('lg'),

                // We can add other actions for other task types here...
            ])
            ->defaultSort('due_at', 'asc');
    }
}
