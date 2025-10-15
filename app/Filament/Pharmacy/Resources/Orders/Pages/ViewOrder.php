<?php

namespace App\Filament\Pharmacy\Resources\Orders\Pages;

use App\Filament\Pharmacy\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Src\Order\Application\Actions\DownloadInvoiceAction;
use Src\Order\Application\Actions\DownloadInvoiceAsFpdfAction;
use Src\Order\Application\Actions\LogOrderEventAction;
use Src\Order\Domain\Models\Invoice;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirm_order')
                ->label('Confirm Order')
                ->color('success')->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->action(fn (LogOrderEventAction $logAction) => $this->updateStatus('confirmed', $logAction))
                ->visible(fn (Invoice $record): bool => $record->status === 'paid'),

            Action::make('mark_dispatched')
                ->label('Mark as Dispatched')
                ->color('info')->icon('heroicon-o-truck')
                ->requiresConfirmation()
                ->action(fn (LogOrderEventAction $logAction) => $this->updateStatus('dispatched', $logAction))
                ->visible(fn (Invoice $record): bool => $record->status === 'confirmed'),

            Action::make('mark_delivered')
                ->label('Mark as Delivered')
                ->color('primary')->icon('heroicon-o-check-badge')
                ->requiresConfirmation()
                ->action(fn (LogOrderEventAction $logAction) => $this->updateStatus('delivered', $logAction))
                ->visible(fn (Invoice $record): bool => $record->status === 'dispatched'),

            Action::make('cancel_order')
                ->label('Cancel Order')
                ->color('danger')->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('cancellation_reason')->required(),
                ])
                ->action(function (array $data, LogOrderEventAction $logAction) {
                    $this->updateStatus('cancelled', $logAction, ['reason' => $data['cancellation_reason']]);
                })
                ->visible(fn (Invoice $record): bool => ! in_array($record->status, ['delivered', 'cancelled'])),
            // Action::make('download_invoice')
            //     ->label('Download PDF')
            //     ->icon('heroicon-o-arrow-down-tray')
            //     ->color('gray')
            //     ->action(function (Invoice $record) {
            //         return app(DownloadInvoiceAction::class)->execute($record);
            //     }),
            Action::make('download_invoice_fpdf')
                ->label('Download PDF (Fast)')
                ->icon('heroicon-o-document')
                ->color('gray')
                ->action(function (Invoice $record) {
                    return app(DownloadInvoiceAsFpdfAction::class)->execute($record);
                }),
        ];
    }

    private function updateStatus(string $status, LogOrderEventAction $logAction, array $metadata = []): void
    {
        $logAction->execute($this->record, $status, $metadata);
        $this->record->update(['status' => $status]);
        $this->refreshFormData([]);
    }

    public function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->record($this->record)
            ->schema([
                // TOP-LEVEL GRID
                Grid::make(3)->schema([
                    // Section 1: Order Details
                    Section::make('Order Details')->schema([
                        TextEntry::make('invoice_number')->label('Order #'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'paid', 'confirmed' => 'warning',
                                'dispatched' => 'info',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => Str::title(str_replace('_', ' ', $state))),
                        TextEntry::make('created_at')->label('Date Placed')->dateTime(),
                    ])->columnSpan(1),

                    // Section 2: Patient & Shipping
                    Section::make('Patient & Shipping')->schema([
                        TextEntry::make('patient.full_name')->label('Patient Name'),
                        TextEntry::make('shipping_name')->label('Recipient Name'),
                        TextEntry::make('shipping_phone')->label('Recipient Phone'),
                        TextEntry::make('shipping_location_area')->label('Shipping Address'),
                    ])->columnSpan(2),
                ]),

                // ITEMS & FINANCIALS SECTION
                Section::make('Items & Financials')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('description')->columnSpan(2),
                                TextEntry::make('quantity')->numeric(),
                                TextEntry::make('price')->money('NGN'),
                                TextEntry::make('total')->money('NGN'),
                            ])->columns(5),

                        // Financial Summary aligned to the right
                        Grid::make(1)->schema([
                            TextEntry::make('subtotal')->money('NGN'),
                            TextEntry::make('delivery_fee')->money('NGN'),
                            TextEntry::make('total')->money('NGN')->weight('bold')->size('lg'),
                        ])->columnSpan('full'),
                    ]),

                // ORDER HISTORY SECTION
                Section::make('Order History')
                    ->schema([
                        RepeatableEntry::make('events')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('created_at')->dateTime()->label('Timestamp'),
                                TextEntry::make('event_type')
                                    ->label('Event')
                                    ->formatStateUsing(fn (string $state): string => Str::title(str_replace('_', ' ', $state))),
                                TextEntry::make('user.name')->label('Triggered By'),
                                TextEntry::make('metadata.reason')->label('Reason')->visible(fn ($state) => ! empty($state)),
                            ])->columns(4),
                    ])->collapsible()->collapsed(),
            ]);
    }
}
