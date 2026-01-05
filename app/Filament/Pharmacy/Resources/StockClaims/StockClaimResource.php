<?php

namespace App\Filament\Pharmacy\Resources\StockClaims;

use App\Filament\Pharmacy\Resources\StockClaims\Pages\ManageStockClaims;
use App\Models\StockClaim;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Shared\Infrastructure\Services\TelegramService;
use UnitEnum;

class StockClaimResource extends Resource
{
    protected static ?string $model = StockClaim::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::InboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Growth & Earnings';

    protected static ?string $navigationLabel = 'Incoming B2B Requests';

    protected static ?string $modelLabel = 'Stock Request';

    protected static ?int $navigationSort = 4;

    /**
     * Badge to show pending requests count in the sidebar.
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()
            ->where('status', 'pending_approval')
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * Security: Only show claims where the *product batch* belongs to the *current user's pharmacy*.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('batch.pharmacyProduct', function (Builder $query) {
                $query->where('pharmacy_id', Filament::auth()->user()->pharmacy_id);
            });
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('claimer.name')
                    ->label('Requesting Pharmacist')
                    ->description(fn (StockClaim $record) => $record->pharmacy->name)
                    ->searchable(),

                TextColumn::make('batch.pharmacyProduct.name')
                    ->label('Product')
                    ->description(fn (StockClaim $record) => 'Exp: '.$record->batch->expiry_date->format('M Y'))
                    ->wrap(),

                TextColumn::make('quantity_claimed')
                    ->label('Qty Requested')
                    ->badge()
                    ->color('info'),

                TextColumn::make('agreed_commission_percent')
                    ->label('Commission')
                    ->formatStateUsing(fn ($state) => $state.'%')
                    ->alignCenter(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending_approval' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'sold' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucwords(str_replace('_', ' ', $state))),

                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (StockClaim $record) => $record->status === 'pending_approval')
                    ->action(function (StockClaim $record, TelegramService $telegramService) {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                        ]);

                        // Notify the Claimer
                        $claimer = $record->claimer;
                        $productName = $record->batch->pharmacyProduct->name;

                        Notification::make()
                            ->title('Request Approved!')
                            ->body("Your request to sell **{$productName}** has been approved. You can now arrange pickup/delivery.")
                            ->success()
                            ->actions([
                                Action::make('contact_owner')
                                    ->label('Contact Owner')
                                    ->url("https://wa.me/{$record->batch->pharmacyProduct->user->phone}", shouldOpenInNewTab: true),
                            ])
                            ->sendToDatabase($claimer);

                        if ($claimer->telegram_chat_id) {
                            $telegramService->sendMessageToUser($claimer, "✅ *Request Approved*\n\nThe owner has approved your request for *{$productName}*. Please proceed with the arrangement.");
                        }

                        Notification::make()->title('Request Approved')->success()->send();
                    }),

                // --- ACTION: REJECT ---
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (StockClaim $record) => $record->status === 'pending_approval')
                    ->action(function (StockClaim $record, TelegramService $telegramService) {
                        $record->update(['status' => 'rejected']);

                        // Notify Claimer
                        $claimer = $record->claimer;
                        $productName = $record->batch->pharmacyProduct->name;

                        Notification::make()
                            ->title('Request Declined')
                            ->body("Your request for {$productName} was declined by the owner.")
                            ->danger()
                            ->sendToDatabase($claimer);

                        if ($claimer->telegram_chat_id) {
                            $telegramService->sendMessageToUser($claimer, "❌ *Request Declined*\n\nThe owner has declined your request for *{$productName}*.");
                        }
                    }),

                // --- ACTION: WHATSAPP CHAT ---
                Action::make('chat')
                    ->label('Chat')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('gray')
                    ->url(fn (StockClaim $record) => "https://wa.me/{$record->claimer->phone}?text=Hi, regarding your request for {$record->batch->pharmacyProduct->name}...", true),
                // ->toolbarActions([
                //     BulkActionGroup::make([
                //         DeleteBulkAction::make(),
                //     ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageStockClaims::route('/'),
        ];
    }
}
