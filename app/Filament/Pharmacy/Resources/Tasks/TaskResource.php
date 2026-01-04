<?php

namespace App\Filament\Pharmacy\Resources\Tasks;

use App\Filament\Pharmacy\Resources\Patients\PatientResource;
use App\Filament\Pharmacy\Resources\Tasks\Pages\ManageTasks;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Src\Audit\Domain\Models\PharmacistActionLog;
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
        return static::getModel()::query()
            ->where('assigned_to_user_id', Auth::id())
            ->where('status', 'pending')
            // Only count tasks that are NOT overdue
            ->where(fn (Builder $query) => $query->whereNull('due_at')->orWhere('due_at', '>=', now()))
            ->count();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('30s')
            ->columns([
                // 1. Task Information
                TextColumn::make('taskDefinition.name')
                    ->label('Task Details')
                    ->searchable()
                    ->sortable()
                    ->weight(fn (Task $record) => $record->taskDefinition->key === 'PRODUCT_INTEGRITY_CHECK' ? 'bold' : 'medium')
                    ->color(fn (Task $record) => $record->taskDefinition->key === 'PRODUCT_INTEGRITY_CHECK' ? 'danger' : 'primary')
                    ->description(fn (Task $record) => str($record->description)->limit(50))
                    ->wrap(),

                // 2. Subject
                TextColumn::make('subjects_description')
                    ->label('Subject / Patient')
                    ->wrap()
                    ->icon('heroicon-m-user')
                    ->url(function (Task $record): ?string {
                        if ($record->subjectable instanceof Patient) {
                            return PatientResource::getUrl('view', ['record' => $record->subjectable]);
                        }

                        return null;
                    })
                    ->color('gray'),

                // 3. Due Date
                TextColumn::make('due_at')
                    ->label('Deadline')
                    ->date('M j, Y H:i')
                    ->description(fn (Task $record) => $record->due_at ? $record->due_at->diffForHumans() : null)
                    ->sortable(),

                // 4. Points
                TextColumn::make('taskDefinition.points')
                    ->label('Points')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-s-star'),

                // 5. Status
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'completed' => 'heroicon-o-check-circle',
                        default => 'heroicon-o-question-mark-circle',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                    ])
                    ->default('pending'),
            ])
            ->recordActions([
                // ACTION 1: Send WhatsApp
                Action::make('send_and_complete')
                    ->label('Review & Send')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->visible(fn (Task $record): bool => $record->status === 'pending' && $record->taskDefinition->key === 'PATIENT_COUNSELING_FOLLOW_UP')
                    ->modalHeading('Send Counseling Message')
                    ->modalDescription('Review the message below. "Open WhatsApp" prepares the text on your device. Confirm completion afterwards.')
                    ->schema([
                        Forms\Components\Textarea::make('message_to_send')
                            ->label('Message Content')
                            ->rows(6)
                            ->default(fn (Task $record) => $record->description)
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->modalFooterActions(function (Task $record) {
                        $patient = $record->subjectable;
                        if (! $patient instanceof Patient || ! $patient->phone) {
                            return [Action::make('close')->label('Error: Patient missing phone number')->modalClose()->color('danger')];
                        }

                        $whatsappUrl = 'https://wa.me/'.$patient->phone.'?text='.urlencode($record->description);

                        return [
                            Action::make('open_whatsapp')
                                ->label('1. Open WhatsApp')
                                ->icon('heroicon-s-paper-airplane')
                                ->url($whatsappUrl, shouldOpenInNewTab: true)
                                ->color('info'),

                            Action::make('confirm_completion')
                                ->label('2. Confirm Sent')
                                ->color('success')
                                ->icon('heroicon-s-check')
                                ->action(function (Task $record, AwardPointsAction $awardPoints) {
                                    $user = Auth::user();

                                    PharmacistActionLog::create([
                                        'user_id' => $user->id,
                                        'task_id' => $record->id,
                                        'subjectable_id' => $record->subjectable->id,
                                        'subjectable_type' => $record->subjectable->getMorphClass(),
                                        'action_type' => 'PATIENT_COUNSELING_SENT',
                                        'description' => "Counseling message sent for '{$record->title}'",
                                        'metadata' => ['message_sent' => $record->description],
                                    ]);

                                    $record->update(['status' => 'completed', 'completed_at' => now()]);
                                    $awardPoints->execute($user, $record->taskDefinition->key, $record);

                                    Notification::make()->title('Task Completed')->body("+{$record->taskDefinition->points} Points")->success()->send();
                                })
                                ->requiresConfirmation(),
                        ];
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                // ACTION 2: General Completion
                Action::make('complete_task')
                    ->label('Complete Task')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->hidden(fn (Task $record): bool => $record->taskDefinition->key === 'PATIENT_COUNSELING_FOLLOW_UP')
                    ->visible(fn (Task $record): bool => $record->status === 'pending')
                    ->modalWidth(fn (Task $record) => $record->taskDefinition->key === 'TECHNICIAN_PRICE_VERIFY' ? '4xl' : 'lg')
                    ->schema(function (Task $record): array {
                        $taskKey = $record->taskDefinition->key;

                        if (str_starts_with($taskKey, 'PATIENT_FOLLOW_UP')) {
                            return [
                                Textarea::make('notes')
                                    ->required()
                                    ->label('Follow-up Outcome / Notes')
                                    ->placeholder('Enter details about the interaction...')
                                    ->rows(4),
                            ];
                        }

                        if ($taskKey === 'TECHNICIAN_PRICE_VERIFY') {
                            $products = $record->pharmacyProducts()
                                ->with('medicationVariant.medication')
                                ->get();

                            return [
                                Section::make('Update Product Prices')
                                    ->description('Review and update prices below. Prices will be updated immediately upon confirmation.')
                                    ->schema([
                                        Forms\Components\Repeater::make('products')
                                            ->label('Product List')
                                            ->schema([
                                                Forms\Components\Hidden::make('id'),
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Product Name')
                                                    ->disabled()
                                                    ->dehydrated(false),
                                                Forms\Components\TextInput::make('new_price')
                                                    ->numeric()
                                                    ->required()
                                                    ->prefix('₦')
                                                    ->label('New Price'),
                                            ])
                                            ->columns(2)
                                            ->addable(false)
                                            ->deletable(false)
                                            ->reorderable(false)
                                            ->default($products->map(fn ($p) => [
                                                'id' => $p->id,
                                                'name' => $p->name,
                                                'new_price' => $p->price / 100,
                                            ])->all()),
                                    ]),
                            ];
                        }

                        if ($taskKey === 'PRODUCT_INTEGRITY_CHECK') {
                            $products = $record->pharmacyProducts()->with('medicationVariant.medication')->get();

                            return [
                                Section::make('Instructions')
                                    ->schema([
                                        TextEntry::make('instructions')
                                            ->hiddenLabel()
                                            ->state($record->instructions)
                                            ->prose(),
                                    ]),

                                Forms\Components\CheckboxList::make('confirmation')
                                    ->label('Checklist Confirmation')
                                    ->options($products->mapWithKeys(fn ($p) => [$p->id => $p->name]))
                                    ->helperText('Check each box to confirm the integrity action was performed.')
                                    ->required()
                                    ->bulkToggleable()
                                    ->rules(['array', function ($attribute, $value, $fail) use ($products) {
                                        if (count($value) !== $products->count()) {
                                            $fail('You must verify all listed products.');
                                        }
                                    }]),
                            ];
                        }

                        return [
                            TextEntry::make('confirmation_text')
                                ->content('Are you sure you want to mark this task as completed?'),
                        ];
                    })
                    ->action(function (Task $record, array $data, AwardPointsAction $awardPoints) {
                        $taskKey = $record->taskDefinition->key;
                        $user = Auth::user();

                        DB::transaction(function () use ($record, $data, $user, $taskKey, $awardPoints) {
                            if ($taskKey === 'PRODUCT_INTEGRITY_CHECK') {
                                $confirmedProducts = PharmacyProduct::find(array_keys($data['confirmation']));
                                PharmacistActionLog::create([
                                    'user_id' => $user->id,
                                    'subjectable_id' => $record->id,
                                    'subjectable_type' => $record->getMorphClass(),
                                    'action_type' => 'PRODUCT_INTEGRITY_CHECK_CONFIRMED',
                                    'description' => "Confirmed integrity check: '{$record->title}'",
                                    'metadata' => ['confirmed_ids' => $confirmedProducts->pluck('id')->all()],
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
                                foreach ($data['products'] as $productData) {
                                    $product = PharmacyProduct::find($productData['id']);
                                    if ($product) {
                                        $newPriceInKobo = (int) ($productData['new_price'] * 100);
                                        if ($product->price !== $newPriceInKobo) {
                                            $product->update(['price' => $newPriceInKobo]);
                                            PriceHistory::create([
                                                'pharmacy_product_id' => $product->id,
                                                'price' => $newPriceInKobo,
                                                'updated_by_user_id' => $user->id,
                                            ]);
                                        }
                                    }
                                }
                            }

                            $record->update(['status' => 'completed', 'completed_at' => now()]);
                            $awardPoints->execute($user, $taskKey, $record);
                        });

                        Notification::make()
                            ->title('Task Completed')
                            ->body("You earned {$record->taskDefinition->points} points!")
                            ->success()
                            ->send();
                    })
                    ->modalHeading(fn (Task $record) => $record->taskDefinition->name),
            ])
            ->defaultSort('due_at', 'asc')
            ->toolbarActions([]);
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
            ->where('assigned_to_user_id', Filament::auth()->id())
            // Logic: Hide Pending Overdue Tasks.
            // Show if: Status is NOT pending (i.e., completed) OR Due Date is in the future (or null)
            ->where(function (Builder $query) {
                $query->where('status', '!=', 'pending')
                    ->orWhereNull('due_at')
                    ->orWhere('due_at', '>=', now());
            });
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
