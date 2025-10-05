<?php

namespace App\Filament\Technician\Widgets;

use App\Filament\Technician\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Src\Order\Application\Actions\LogOrderEventAction;
use Src\Order\Domain\Models\Invoice;

class OrdersToPackWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Orders Ready for Packing & Dispatch';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->where('pharmacy_id', Filament::auth()->user()->pharmacy_id)
                    ->where('status', 'confirmed') // Only show orders a pharmacist has confirmed
            )
            ->columns([
                TextColumn::make('invoice_number')->label('Order #'),
                TextColumn::make('patient.full_name'),
                TextColumn::make('created_at')->label('Confirmed At')->since(),
            ])
            ->recordActions([
                Action::make('view_order')
                    ->label('View Details')
                    ->url(fn (Invoice $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-arrow-right-circle'),
                // --- ACTION 1: View Order Details ---
                ViewAction::make()
                    ->url(fn (Invoice $record): string => OrderResource::getUrl('view', ['record' => $record])),

                // --- ACTION 2: Mark as Dispatched ---
                Action::make('dispatch')
                    ->label('Mark as Dispatched')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Dispatch Order')
                    ->modalDescription('Are you sure you have packed and dispatched this order?')
                    ->action(function (Invoice $record, LogOrderEventAction $logAction) {
                        // We reuse our existing robust action
                        $logAction->execute($record, 'dispatched');
                        $record->update(['status' => 'dispatched']);
                        $this->dispatch('update-stats'); // Refresh any other widgets
                    }),
            ])->emptyStateHeading('No orders to pack')
            ->emptyStateDescription('New orders confirmed by a pharmacist will appear here.');
    }
}
