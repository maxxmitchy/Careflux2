<?php

namespace App\Filament\Pharmacy\Resources\Tasks;

use App\Filament\Pharmacy\Resources\Patients\PatientResource;
use App\Filament\Pharmacy\Resources\Tasks\Pages\ManageTasks;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Src\Gamification\Application\Actions\AwardPointsAction;
use Src\Gamification\Domain\Models\Task;
use Src\Patient\Domain\Models\Patient;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'My Tasks';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('assigned_to_user_id', Auth::id())->where('status', 'pending')->count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('taskDefinition.name')->label('Task'),
                TextColumn::make('subjectable_text')->label('Subject')
                    ->url(function (Task $record): ?string {
                        if ($record->subjectable instanceof Patient) {
                            return PatientResource::getUrl('view', ['record' => $record->subjectable]);
                        }

                        return null;
                    }),
                TextColumn::make('taskDefinition.points')->label('Points')->icon('heroicon-s-star'),
                TextColumn::make('due_at')->label('Due')->since()->sortable(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // EditAction::make(),
                // DeleteAction::make(),
                Action::make('complete_task')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Task $record): bool => $record->status === 'pending')
                    ->schema(function (Task $record): array {
                        $taskKey = $record->taskDefinition->key;

                        if (str_starts_with($taskKey, 'PATIENT_FOLLOW_UP')) {
                            return [
                                Textarea::make('notes')->required()->label('Follow-up Notes')->rows(4),
                            ];
                        }

                        if ($taskKey === 'TECHNICIAN_PRICE_VERIFY') {
                            return [
                                TextEntry::make('product_name')
                                    ->label('Product')
                                    ->state($record->subjectable?->name),
                                TextInput::make('new_price')
                                    ->label('New Verified Price (in Naira)')
                                    ->numeric()->required()->prefix('₦')
                                    ->minValue(1),
                            ];
                        }

                        if ($taskKey === 'TECHNICIAN_EXPIRY_LOG') {
                            return [
                                TextEntry::make('product_name')
                                    ->label('Product')
                                    ->state($record->subjectable?->name),
                                DatePicker::make('expiry_date')->required()->native(false),
                                TextInput::make('quantity')->numeric()->integer()->required()->minValue(1)
                                    ->helperText('The number of units with this expiry date.'),
                            ];
                        }

                        // Default: For tasks that only need confirmation.
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
                            ->body("You have been awarded {$record->taskDefinition->points} points.")
                            ->success()
                            ->send();
                    })
                    ->modalHeading(fn (Task $record) => 'Complete Task: '.$record->taskDefinition->name),
                Action::make('log_follow_up')
                    ->label('Complete Task')
                    ->icon('heroicon-o-phone-arrow-up-right')
                    ->schema([
                        Select::make('type')->options([
                            'PATIENT_FOLLOW_UP_48HR' => '48-Hour Check-in',
                            'PATIENT_FOLLOW_UP_7DAY' => '7-Day Follow-up',
                        ])->required(),
                        Textarea::make('notes')->required(),
                    ])
                    ->action(function (Task $record, array $data, AwardPointsAction $awardPoints) {
                        if (! $record->subjectable instanceof Patient) {
                            return;
                        }

                        $record->subjectable->interactions()->create([
                            'user_id' => Auth::id(), 'type' => $data['type'], 'notes' => $data['notes'],
                        ]);

                        $record->update(['status' => 'completed', 'completed_at' => now()]);
                        $awardPoints->execute(Auth::user(), $record->taskDefinition->key, $record);

                        Notification::make()->title('Task Completed & Points Awarded!')->success()->send();
                    })
                    ->visible(fn (Task $record) => $record->status === 'pending' && str_starts_with($record->taskDefinition->key, 'PATIENT_FOLLOW_UP')),
            ])->defaultSort('due_at', 'asc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTasks::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('assigned_to_user_id', Filament::auth()->id());
    }

    /**
     * Pharmacists and technicians cannot create tasks from this resource.
     */
    public static function canCreate(): bool
    {
        return false;
    }
}
