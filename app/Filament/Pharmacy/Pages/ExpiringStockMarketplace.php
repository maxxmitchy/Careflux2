<?php

namespace App\Filament\Pharmacy\Pages;

use App\Models\StockClaim;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Src\Pharmacy\Domain\Models\ProductBatch;
use Src\Shared\Infrastructure\Services\TelegramService;
use UnitEnum;

class ExpiringStockMarketplace extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Clearance Opportunities';

    protected static ?string $title = 'Near-Expiry Opportunities';

    protected string $view = 'filament.pharmacy.pages.expiring-stock-marketplace';

    protected static string|UnitEnum|null $navigationGroup = 'Growth & Earnings';

    protected static ?int $navigationSort = 1;

    public function table(Table $table): Table
    {
        $currentPharmacyId = Filament::auth()->user()->pharmacy_id;

        return $table
            ->query(
                ProductBatch::query()
                    ->with(['pharmacyProduct.pharmacy', 'pharmacyProduct.medicationVariant.medication'])
                    ->expiringSoon(6)
                    ->whereHas(
                        'pharmacyProduct',
                        fn ($q) => $q->where('pharmacy_id', '!=', $currentPharmacyId)
                    )
            )
            ->columns([
                Tables\Columns\ImageColumn::make('pharmacyProduct.image')
                    ->label('Product'),

                Tables\Columns\TextColumn::make('pharmacyProduct.name')
                    ->label('Medication')
                    ->description(fn (ProductBatch $record) => 'Sold by: '.$record->pharmacyProduct->pharmacy->name
                    )
                    ->searchable(),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable()
                    ->color('danger')
                    ->label('Expires'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Availability')
                    ->formatStateUsing(function (ProductBatch $record) {
                        $total = $record->quantity;
                        $available = $record->available_to_claim;
                        $pending = $total - $available;

                        return $pending > 0
                            ? "{$available} left ({$pending} pending)"
                            : $available;
                    })
                    ->badge()
                    ->color(fn (ProductBatch $record) => $record->available_to_claim > 0 ? 'success' : 'gray'
                    ),

                Tables\Columns\TextColumn::make('potential_commission')
                    ->label('Your 30% Bonus')
                    ->money('NGN')
                    ->state(function (ProductBatch $record) {
                        $sellingPrice = $record->pharmacyProduct->price;
                        $costPrice = $record->cost_price ?? 0;
                        $profit = max(0, $sellingPrice - $costPrice);

                        return ($profit * 0.30) / 100;
                    })
                    ->color('success')
                    ->weight('bold'),
            ])
            ->recordActions([
                Action::make('claim_stock')
                    ->label('Help Sell')
                    ->button()
                    ->icon('heroicon-o-hand-raised')
                    ->color('success')

                    // Disable when nothing left
                    ->disabled(fn (ProductBatch $record) => $record->available_to_claim <= 0
                    )

                    ->modalHeading('Claim Stock for Sale')
                    ->modalDescription(fn (ProductBatch $record) => "There are currently {$record->available_to_claim} units available for claim."
                    )

                    ->schema([
                        TextInput::make('quantity_to_claim')
                            ->label('Quantity to Claim')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(fn (ProductBatch $record) => $record->available_to_claim
                            )
                            ->default(fn (ProductBatch $record) => $record->available_to_claim
                            )
                            ->suffix('units'),
                    ])

                    ->action(function (
                        ProductBatch $record,
                        array $data,
                        TelegramService $telegramService
                    ) {
                        // Re-fetch to avoid race conditions
                        $record->refresh();

                        if ($data['quantity_to_claim'] > $record->available_to_claim) {
                            Notification::make()
                                ->title('Stock Unavailable')
                                ->body('Someone else just claimed this stock. Please try again.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $buyer = Filament::auth()->user();
                        $owner = $record->pharmacyProduct->user;

                        // Prevent duplicate pending claims
                        $existingClaim = StockClaim::where('product_batch_id', $record->id)
                            ->where('claiming_user_id', $buyer->id)
                            ->where('status', 'pending_approval')
                            ->first();

                        if ($existingClaim) {
                            Notification::make()
                                ->title('Duplicate Request')
                                ->body(
                                    "You already have a pending request for {$existingClaim->quantity_claimed} units of this batch."
                                )
                                ->warning()
                                ->send();

                            return;
                        }

                        // Create claim
                        $claim = StockClaim::create([
                            'product_batch_id' => $record->id,
                            'claiming_user_id' => $buyer->id,
                            'claiming_pharmacy_id' => $buyer->pharmacy_id,
                            'quantity_claimed' => $data['quantity_to_claim'],
                            'status' => 'pending_approval',
                            'agreed_commission_percent' => 30.00,
                        ]);

                        // Prepare notification data
                        $productName = $record->pharmacyProduct->name;
                        $expiry = $record->expiry_date->format('M Y');
                        $qty = $data['quantity_to_claim'];
                        $buyerPharmacy = $buyer->pharmacy->name;

                        // In-app notification
                        Notification::make()
                            ->title('Stock Assistance Request')
                            ->body(
                                "**{$buyer->name}** from **{$buyerPharmacy}** wants to help sell **{$qty} units** of **{$productName}** (Exp: {$expiry})."
                            )
                            ->warning()
                            ->actions([
                                Action::make('contact')
                                    ->label('Chat on WhatsApp')
                                    ->url(
                                        "https://wa.me/{$buyer->phone}?text=Hi, I saw your request to help sell {$productName}.",
                                        shouldOpenInNewTab: true
                                    ),
                            ])
                            ->sendToDatabase($owner);

                        // Telegram alert
                        if ($owner->telegram_chat_id) {
                            $msg =
                                "🤝 *New Business Opportunity*\n\n".
                                "*{$buyer->name}* ({$buyerPharmacy}) wants to help sell your expiring stock:\n".
                                "💊 *Product:* {$productName}\n".
                                "📦 *Qty:* {$qty}\n".
                                "📅 *Exp:* {$expiry}\n\n".
                                'Log in to Careflux to approve or contact them directly.';

                            $telegramService->sendMessageToUser($owner, $msg);
                        }

                        // Feedback to claimer
                        Notification::make()
                            ->title('Request Sent')
                            ->body('The pharmacy owner has been notified. You will be alerted upon approval.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
