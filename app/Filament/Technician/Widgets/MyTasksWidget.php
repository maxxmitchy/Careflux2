<?php

namespace App\Filament\Technician\Widgets;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Src\Gamification\Application\Actions\AwardPointsAction;
use Src\Gamification\Domain\Models\Task; // <-- Import the Forms namespace
use Src\Patient\Domain\Models\Patient;
use Src\Pharmacy\Domain\Models\PharmacyProduct; // <-- Import Builder
use Src\Pharmacy\Domain\Models\ProductExpiry; // <-- Import DB for transaction

class MyTasksWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'My Pending Tasks';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->where('assigned_to_user_id', Filament::auth()->id())
                    ->where('status', 'pending')
                    // --- 1. THE FIX: Eager-load all necessary relationships ---
                    ->with([
                        'taskDefinition',
                        'subjectable',
                        'pharmacyProducts.medicationVariant.medication',
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('taskDefinition.name')
                    ->label('Task'),

                // --- 2. THE FIX: Use the robust accessor for subject display ---
                Tables\Columns\TextColumn::make('subjects_description')
                    ->label('Subject(s)')
                    ->wrap(),

                Tables\Columns\TextColumn::make('due_at')
                    ->label('Due')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                // --- 3. THE FIX: Replace all old actions with the single, intelligent action ---
                Action::make('complete_task')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Task $record): bool => $record->status === 'pending')

                    // --- DYNAMIC FORM LOGIC ---
                    ->schema(function (Task $record): array {
                        $taskKey = $record->taskDefinition->key;

                        if (str_starts_with($taskKey, 'PATIENT_FOLLOW_UP')) {
                            return [
                                Forms\Components\Textarea::make('notes')
                                    ->required()
                                    ->label('Follow-up Notes')
                                    ->rows(4),
                            ];
                        }

                        if ($taskKey === 'TECHNICIAN_PRICE_VERIFY') {
                            // Preload products for batch editing
                            $products = $record->pharmacyProducts()
                                ->with('medicationVariant.medication')
                                ->get();

                            return [
                                Forms\Components\Repeater::make('products')
                                    ->schema([
                                        Hidden::make('id'),
                                        TextEntry::make('name')->label('Product'),
                                        Forms\Components\TextInput::make('new_price')
                                            ->numeric()
                                            ->required()
                                            ->prefix('₦')
                                            ->label('New Price'),
                                    ])
                                    ->columns(2)
                                    ->addable(false)
                                    ->deletable(false)
                                    ->default(
                                        $products->map(fn ($p) => [
                                            'id' => $p->id,
                                            'name' => $p->name,
                                        ])->all()
                                    ),
                            ];
                        }

                        if (str_starts_with($record->taskDefinition->key, 'TECHNICIAN_EXPIRY_LOG')) {
                            $products = $record->pharmacyProducts()->with('medicationVariant.medication')->get();

                            return [
                                Repeater::make('products')
                                    ->label('Products to Check for Expiry')
                                    ->schema([
                                        Hidden::make('id'),
                                        TextEntry::make('name'),
                                        DatePicker::make('expiry_date')
                                            ->label('Expiry Date')
                                            ->required()
                                            ->native(false),
                                        TextInput::make('quantity')
                                            ->numeric()->integer()->required()->minValue(1)
                                            ->helperText('Number of units with this date.'),
                                    ])
                                    ->columns(3)
                                    ->addable(false)->deletable(false)
                                    ->default($products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])->all()),
                            ];
                        }

                        return []; // Default for simple tasks
                    })

                    // Confirmation modal only for manual tasks
                    ->requiresConfirmation(fn (Task $record) => ! in_array(
                        $record->taskDefinition->key,
                        ['PATIENT_FOLLOW_UP_48HR', 'PATIENT_FOLLOW_UP_7DAY', 'TECHNICIAN_PRICE_VERIFY']
                    )
                    )

                    ->action(function (Task $record, array $data, AwardPointsAction $awardPoints) {
                        $taskKey = $record->taskDefinition->key;
                        $user = Auth::user();

                        if (str_starts_with($taskKey, 'PATIENT_FOLLOW_UP')) {
                            if ($record->subjectable instanceof Patient) {
                                $record->subjectable->interactions()->create([
                                    'user_id' => $user->id,
                                    'type' => $record->taskDefinition->name,
                                    'notes' => $data['notes'],
                                ]);
                            }
                        } elseif ($taskKey === 'TECHNICIAN_EXPIRY_LOG') {
                            DB::transaction(function () use ($data, $user) {
                                foreach ($data['products'] as $productData) {
                                    $product = PharmacyProduct::find($productData['id']);
                                    if ($product) {
                                        // Create a new expiry record for this batch
                                        ProductExpiry::create([
                                            'pharmacy_product_id' => $product->id,
                                            'expiry_date' => $productData['expiry_date'],
                                            'quantity' => $productData['quantity'],
                                            'logged_by_user_id' => $user->id,
                                        ]);
                                    }
                                }
                            });
                        } elseif ($taskKey === 'TECHNICIAN_EXPIRY_LOG') {
                            // Example: expiry log could save remarks to ProductExpiry model
                            ProductExpiry::create([
                                'remarks' => $data['remarks'] ?? '',
                                'logged_by_user_id' => $user->id,
                            ]);
                        }

                        // --- UNIVERSAL COMPLETION LOGIC ---
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);

                        $awardPoints->execute($user, $taskKey, $record);

                        Notification::make()
                            ->title('Task Completed!')
                            ->body("You earned {$record->taskDefinition->points} points.")
                            ->success()
                            ->send();
                    })
                    ->modalHeading(fn (Task $record) => $record->taskDefinition->name)
                    ->modalWidth('lg'),
                // --- END OF FIX ---
            ])
            ->defaultSort('due_at', 'asc');
    }
}
