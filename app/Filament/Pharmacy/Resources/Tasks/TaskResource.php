<?php

namespace App\Filament\Pharmacy\Resources\Tasks;

use App\Filament\Pharmacy\Resources\Patients\PatientResource;
use App\Filament\Pharmacy\Resources\Tasks\Pages\ManageTasks;
use App\Models\PharmacistActionLog;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Src\Gamification\Application\Actions\AwardPointsAction;
use Src\Gamification\Domain\Models\Task;
use Src\Patient\Domain\Models\Patient;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Pharmacy\Domain\Models\PriceHistory;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'My Tasks';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('assigned_to_user_id', Auth::id())
            ->where('status', 'pending')
            ->count();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('taskDefinition.name')
                    ->label('Task')->searchable()->wrap(),

                TextColumn::make('taskDefinition.name')
                    ->color(fn (Task $record) => $record->taskDefinition->key === 'PRODUCT_INTEGRITY_CHECK' ? 'danger' : null)
                    ->weight(fn (Task $record) => $record->taskDefinition->key === 'PRODUCT_INTEGRITY_CHECK' ? 'bold' : null),

                Tables\Columns\TextColumn::make('subjects_description')
                    ->label('Subject(s)')->wrap()
                    ->url(function (Task $record): ?string {
                        if ($record->subjectable instanceof Patient) {
                            return PatientResource::getUrl('view', ['record' => $record->subjectable]);
                        }

                        return null;
                    }),

                Tables\Columns\TextColumn::make('taskDefinition.points')
                    ->label('Points')->icon('heroicon-s-star'),

                Tables\Columns\TextColumn::make('due_at')
                    ->label('Due')->since()->sortable(),

                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->recordActions([
                Action::make('complete_task')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Task $record): bool => $record->status === 'pending')

                    // --- THE DEFINITIVE DYNAMIC FORM ---
                    ->schema(function (Task $record): array {
                        $taskKey = $record->taskDefinition->key;

                        if (str_starts_with($taskKey, 'PATIENT_FOLLOW_UP')) {
                            return [
                                Textarea::make('notes')
                                    ->required()
                                    ->label('Follow-up Notes')
                                    ->rows(4),
                            ];
                        }

                        // --- BATCH EDITING FORM LOGIC ---
                        if ($taskKey === 'TECHNICIAN_PRICE_VERIFY') {
                            $products = $record->pharmacyProducts()
                                ->with('medicationVariant.medication')
                                ->get();

                            return [
                                Forms\Components\Repeater::make('products')
                                    ->schema([
                                        Forms\Components\Hidden::make('id'),
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
                        // --- END BATCH EDITING LOGIC ---

                        if ($taskKey === 'PRODUCT_INTEGRITY_CHECK') {
                            $products = $record->pharmacyProducts()->with('medicationVariant.medication')->get();

                            return [
                                TextEntry::make('instructions')
                                    ->label('Instructions')
                                    ->state($record->description),

                                Forms\Components\CheckboxList::make('confirmation')
                                    ->label('Confirmation Checklist')
                                    ->options(
                                        $products->mapWithKeys(fn ($p) => [$p->id => $p->name])
                                    )
                                    ->helperText('Please check the box for each product to confirm you have completed the required action.')
                                    ->required()
                                    ->rules(['array', function ($attribute, $value, $fail) use ($products) {
                                        if (count($value) !== $products->count()) {
                                            $fail('You must confirm the action for all listed products.');
                                        }
                                    }]),
                            ];
                        }

                        return []; // Default for simple confirmation tasks
                    })

                    ->requiresConfirmation()
                    ->action(function (Task $record, array $data, AwardPointsAction $awardPoints) {
                        $taskKey = $record->taskDefinition->key;
                        $user = Auth::user();

                        if ($taskKey === 'PRODUCT_INTEGRITY_CHECK') {
                            $confirmedProducts = PharmacyProduct::find(array_keys($data['confirmation']));

                            PharmacistActionLog::create([
                                'user_id' => $user->id,
                                'subjectable_id' => $record->id,
                                'subjectable_type' => $record->getMorphClass(),
                                'action_type' => 'PRODUCT_INTEGRITY_CHECK_CONFIRMED',
                                'description' => "Confirmed completion of product integrity check: '{$record->title}'.",
                                'metadata' => [
                                    'alert_title' => $record->title,
                                    'confirmed_product_ids' => $confirmedProducts->pluck('id')->all(),
                                ],
                            ]);
                        }

                        if (str_starts_with($taskKey, 'PATIENT_FOLLOW_UP')) {
                            if ($record->subjectable instanceof Patient) {
                                $record->subjectable->interactions()->create([
                                    'user_id' => $user->id,
                                    'type' => $record->taskDefinition->name,
                                    'notes' => $data['notes'],
                                ]);
                            }
                        } elseif ($taskKey === 'TECHNICIAN_PRICE_VERIFY') {
                            // --- BATCH UPDATE ACTION LOGIC ---
                            DB::transaction(function () use ($data, $user) {
                                foreach ($data['products'] as $productData) {
                                    $product = PharmacyProduct::find($productData['id']);
                                    if ($product) {
                                        $newPriceInKobo = (int) ($productData['new_price'] * 100);
                                        $product->update(['price' => $newPriceInKobo]);

                                        PriceHistory::create([
                                            'pharmacy_product_id' => $product->id,
                                            'price' => $newPriceInKobo,
                                            'updated_by_user_id' => $user->id,
                                        ]);
                                    }
                                }
                            });
                            // --- END BATCH UPDATE LOGIC ---
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
                    ->modalHeading(fn (Task $record) => $record->taskDefinition->name),
            ])
            ->defaultSort('due_at', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTasks::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'assignee',
                'taskDefinition',
                'subjectable',
                'pharmacyProducts.medicationVariant.medication',
            ])
            ->where('assigned_to_user_id', Filament::auth()->id());
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
