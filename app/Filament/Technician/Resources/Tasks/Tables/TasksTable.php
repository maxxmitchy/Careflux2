<?php

namespace App\Filament\Technician\Resources\Tasks\Tables;

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

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('taskDefinition.name')
                    ->label('Task')
                    ->weight(FontWeight::Bold)
                    ->description(fn (Task $record) => $record->taskDefinition->description)
                    ->wrap(),

                TextColumn::make('subjects_description')
                    ->label('Subject(s)')
                    ->limit(40)
                    ->wrap(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'overdue' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('due_at')
                    ->label('Due')
                    ->date('M j, Y')
                    ->sortable()
                    ->color(fn ($record) => ($record->status === 'pending' && $record->due_at < now()) ? 'danger' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                    ])
                    ->default('pending'), // Default to showing pending work
            ])
            ->recordActions([
                ViewAction::make(),

                // --- MAIN COMPLETION ACTION ---
                Action::make('complete_task')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Task $record): bool => $record->status === 'pending')
                    ->modalHeading(fn (Task $record) => 'Complete: '.$record->taskDefinition->name)
                    ->modalWidth('lg')
                    ->schema(function (Task $record): array {
                        $taskKey = $record->taskDefinition->key;

                        // 1. Patient Follow-up
                        if (str_starts_with($taskKey, 'PATIENT_FOLLOW_UP')) {
                            return [
                                Textarea::make('notes')
                                    ->required()
                                    ->label('Follow-up Notes')
                                    ->rows(3)
                                    ->placeholder('Enter details of the conversation...'),
                            ];
                        }

                        // 2. Price Verification
                        if ($taskKey === 'TECHNICIAN_PRICE_VERIFY') {
                            $products = $record->pharmacyProducts()
                                ->with('medicationVariant.medication')
                                ->get();

                            return [
                                Repeater::make('products')
                                    ->schema([
                                        Hidden::make('id'),
                                        TextInput::make('name_display')
                                            ->label('Product')
                                            ->disabled()
                                            ->dehydrated(false),
                                        TextInput::make('new_price')
                                            ->numeric()
                                            ->required()
                                            ->prefix('₦')
                                            ->label('New Price'),
                                    ])
                                    ->columns(2)
                                    ->addable(false)
                                    ->deletable(false)
                                    ->default($products->map(fn ($p) => [
                                        'id' => $p->id,
                                        'name_display' => $p->name,
                                    ])->all()),
                            ];
                        }

                        // 3. Expiry Log
                        if (str_starts_with($taskKey, 'TECHNICIAN_EXPIRY_LOG')) {
                            $products = $record->pharmacyProducts()->with('medicationVariant.medication')->get();

                            return [
                                Repeater::make('products')
                                    ->schema([
                                        Hidden::make('id'),
                                        TextInput::make('name_display')
                                            ->label('Product')
                                            ->disabled()
                                            ->dehydrated(false),
                                        DatePicker::make('expiry_date')
                                            ->required()
                                            ->native(false),
                                        TextInput::make('quantity')
                                            ->numeric()
                                            ->integer()
                                            ->required()
                                            ->minValue(1),
                                    ])
                                    ->columns(3)
                                    ->addable(false)
                                    ->deletable(false)
                                    ->default($products->map(fn ($p) => [
                                        'id' => $p->id,
                                        'name_display' => $p->name,
                                    ])->all()),
                            ];
                        }

                        // 4. New Product Discovery
                        if (str_contains($taskKey, 'TECHNICIAN_NEW_PRODUCT_DISCOVERY')) {
                            return [
                                Repeater::make('discovered_products')
                                    ->label('Identify 10 New Products')
                                    ->helperText('List products supplied that we did not have before.')
                                    ->schema([
                                        TextInput::make('product_name')
                                            ->required()
                                            ->label('Product Name'),
                                        TextInput::make('supplier')
                                            ->label('Supplier (Optional)'),
                                    ])
                                    ->minItems(10)
                                    ->defaultItems(1)
                                    ->columns(2)
                                    ->grid(1),
                            ];
                        }

                        // Default for generic tasks
                        return [];
                    })
                    ->action(function (Task $record, array $data, AwardPointsAction $awardPoints) {
                        $taskKey = $record->taskDefinition->key;
                        $user = Auth::user();

                        DB::transaction(function () use ($record, $data, $taskKey, $user, $awardPoints) {

                            // Save Patient Interaction
                            if (str_starts_with($taskKey, 'PATIENT_FOLLOW_UP') && $record->subjectable instanceof Patient) {
                                $record->subjectable->interactions()->create([
                                    'user_id' => $user->id,
                                    'type' => $record->taskDefinition->name,
                                    'notes' => $data['notes'],
                                ]);
                            }

                            // Save Expiry Logs
                            if (str_starts_with($taskKey, 'TECHNICIAN_EXPIRY_LOG')) {
                                foreach ($data['products'] as $item) {
                                    ProductExpiry::create([
                                        'pharmacy_product_id' => $item['id'],
                                        'expiry_date' => $item['expiry_date'],
                                        'quantity' => $item['quantity'],
                                        'logged_by_user_id' => $user->id,
                                    ]);
                                }
                            }

                            // Save Discovery Results
                            if (str_contains($taskKey, 'TECHNICIAN_NEW_PRODUCT_DISCOVERY')) {
                                $record->update([
                                    'results' => $data['discovered_products'] ?? [],
                                ]);
                            }

                            // Mark as Completed
                            $record->update([
                                'status' => 'completed',
                                'completed_at' => now(),
                            ]);

                            // Award Points
                            $awardPoints->execute($user, $taskKey, $record);
                        });

                        Notification::make()
                            ->title('Task Completed')
                            ->body("You earned {$record->taskDefinition->points} points!")
                            ->success()
                            ->send();
                    }),

                EditAction::make()->visible(fn () => Auth::user()->is_manager),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('due_at', 'asc');
    }
}
