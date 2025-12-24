<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Notifications\StockDecrementFailed; // <-- 1. Import the notification
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade; // <-- 2. Import the facade
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Shared\Domain\Models\User; // <-- 3. Import the User model

class DecrementStockListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderCompleted $event): void
    {
        $invoice = $event->invoice;

        DB::transaction(function () use ($invoice) {
            foreach ($invoice->items as $item) {
                if ($item->pharmacy_product_id) {
                    $product = PharmacyProduct::find($item->pharmacy_product_id);
                    if ($product) {
                        try {
                            $product->decrement('stock', $item->quantity);
                        } catch (\Exception $e) {
                            Log::critical('Failed to decrement stock for product.', [
                                'pharmacy_product_id' => $product->id,
                                'invoice_id' => $invoice->id,
                                'error' => $e->getMessage(),
                            ]);

                            // --- 4. NOTIFICATION IMPLEMENTATION ---
                            // Find all administrators
                            $admins = User::where('is_admin', true)->get();

                            if ($admins->isNotEmpty()) {
                                // Send the actionable notification to all admins
                                NotificationFacade::send($admins, new StockDecrementFailed(
                                    $product,
                                    $invoice,
                                    $item->quantity,
                                    $e->getMessage()
                                ));
                            }
                            // --- END OF IMPLEMENTATION ---
                        }
                    }
                }
            }
        });
    }
}
