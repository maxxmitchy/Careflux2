<?php

namespace App\Listeners;

use App\Events\QuoteProcessed;
use App\Mail\QuoteProcessedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendQuoteProcessedNotification implements ShouldQueue
{
    public function handle(QuoteProcessed $event): void
    {
        $quoteRequest = $event->quoteRequest->load('items.productable');
        Mail::to($quoteRequest->patient_email)->send(new QuoteProcessedMail($quoteRequest));
    }
}
