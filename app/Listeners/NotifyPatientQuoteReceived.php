<?php

namespace App\Listeners;

use App\Events\QuoteRequestSubmitted;
use App\Mail\QuoteRequestConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NotifyPatientQuoteReceived implements ShouldQueue
{
    public function handle(QuoteRequestSubmitted $event): void
    {
        $quoteRequest = $event->quoteRequest->load('items.productable');

        // Send the confirmation email to the email address provided in the form.
        Mail::to($quoteRequest->patient_email)->send(
            new QuoteRequestConfirmationMail($quoteRequest)
        );
    }
}
