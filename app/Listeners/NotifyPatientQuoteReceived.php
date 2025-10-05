<?php

namespace App\Listeners;

use App\Events\QuoteRequestSubmitted;

class NotifyPatientQuoteReceived
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(QuoteRequestSubmitted $event): void
    {
        //
    }
}
