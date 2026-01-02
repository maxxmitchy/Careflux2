<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\Hidden;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Src\Patient\Domain\Models\Patient;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Src\Gamification\Domain\Models\Task;
use Filament\Forms\Components\DatePicker;
// use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Src\Pharmacy\Domain\Models\ProductExpiry;
use Src\Gamification\Application\Actions\AwardPointsAction;
use Filament\Forms\Components\Select; // Needed for Replicate

class TeamTasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // 1. GROUP BY USER: Organizes view by staff member
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

                TextColumn::make('assignee.name')
                    ->label('Assigned To')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('subjects_description')
                    ->label('Subject(s)')
                    ->limit(50)
                    ->wrap(),

                TextColumn::make('due_at')
                    ->date('M j, Y')
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
                    ->relationship('assignee', 'name'),
                SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'completed' => 'Completed']),
            ])
            ->recordActions([
                // 2. REPLICATE ACTION: Maximum efficiency for the manager
                ReplicateAction::make()
                    ->label('Clone')
                    ->excludeAttributes(['status', 'completed_at', 'results'])
                    ->modalHeading('Duplicate Task')
                    ->schema([
                        Select::make('assigned_to_user_id')
                            ->relationship('assignee', 'name')
                            ->label('Assign Clone To')
                            ->required(),
                        DatePicker::make('due_at')
                            ->default(now())
                            ->required(),
                    ])
                    ->beforeReplicaSaved(function (Task $replica, array $data) {
                        $replica->assigned_to_user_id = $data['assigned_to_user_id'];
                        $replica->due_at = $data['due_at'];
                        $replica->status = 'pending';
                    }),

                ViewAction::make(),
                EditAction::make(),

                // 3. ROBUST COMPLETE ACTION
                Action::make('complete_task')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Task $record): bool => $record->status === 'pending')
                    ->modalHeading(fn (Task $record) => 'Complete: ' . $record->taskDefinition->name)
                    ->modalWidth('lg')
                    ->schema(function (Task $record): array {
                        $taskKey = $record->taskDefinition->key;

                        // Patient Follow-up
                        if (str_starts_with($taskKey, 'PATIENT_FOLLOW_UP')) {
                            return [
                                Textarea::make('notes')
                                    ->required()
                                    ->label('Follow-up Notes')
                                    ->rows(3),
                            ];
                        }

                        // Price Verification
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
                                        'name_display' => $p->name
                                    ])->all()),
                            ];
                        }

                        // Expiry Log
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
                                        'name_display' => $p->name
                                    ])->all()),
                            ];
                        }

                        // New Product Discovery
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

                        return [];
                    })
                    ->action(function (Task $record, array $data, AwardPointsAction $awardPoints) {
                        $taskKey = $record->taskDefinition->key;
                        $user = Auth::user(); // The Manager completing the task

                        DB::transaction(function () use ($record, $data, $taskKey, $user, $awardPoints) {
                            
                            // 1. Handle Patient Interactions
                            if (str_starts_with($taskKey, 'PATIENT_FOLLOW_UP') && $record->subjectable instanceof Patient) {
                                $record->subjectable->interactions()->create([
                                    'user_id' => $user->id,
                                    'type' => $record->taskDefinition->name,
                                    'notes' => $data['notes'],
                                ]);
                            }

                            // 2. Handle Expiry
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

                            // 3. Handle Discovery (Save to JSON)
                            if (str_contains($taskKey, 'TECHNICIAN_NEW_PRODUCT_DISCOVERY')) {
                                $record->update([
                                    'results' => $data['discovered_products'] ?? [],
                                ]);
                            }

                            // 4. Mark Complete
                            $record->update([
                                'status' => 'completed',
                                'completed_at' => now()
                            ]);

                            // 5. Award Points (To the Manager doing it, or you could swap $user for $record->assignedTo)
                            $awardPoints->execute($user, $taskKey, $record);
                        });

                        Notification::make()
                            ->title('Task Completed & Points Awarded!')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}