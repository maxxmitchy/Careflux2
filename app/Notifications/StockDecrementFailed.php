<?php

namespace App\Notifications;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Src\Order\Domain\Models\Invoice;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class StockDecrementFailed extends \Illuminate\Notifications\Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public PharmacyProduct $product,
        public Invoice $invoice,
        public int $quantity,
        public string $errorMessage
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database']; // For Filament panel notifications
    }

    /**
     * Get the array representation of the notification for storage.
     */
    public function toArray(object $notifiable): array
    {
        return Notification::make()
            ->title('CRITICAL: Stock Decrement Failed')
            ->body("Failed to decrement stock for '{$this->product->name}' by {$this->quantity} for Order #{$this->invoice->invoice_number}. Manual intervention required.")
            ->danger() // Use 'danger' color for high-priority alerts
            ->icon('heroicon-o-exclamation-triangle')
            ->actions([
                // This provides a direct link for the admin to investigate
                Action::make('view_invoice')
                    ->label('View Invoice')
                    ->url(fn () => OrderResource::getUrl('view', ['record' => $this->invoice]))
                    ->button(),
            ])
            ->getDatabaseMessage();
    }
}
