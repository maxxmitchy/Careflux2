<?php

namespace App\Observers;

use App\Events\OrderCompleted;
use Src\Order\Domain\Models\Invoice;

class InvoiceObserver
{
    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        // Check if the 'status' field was specifically changed in this update,
        // and if its new value is 'paid'.
        if ($invoice->wasChanged('status') && $invoice->status === 'paid') {
            // The order is now considered complete. Dispatch the event.
            OrderCompleted::dispatch($invoice);
        }
    }
}
