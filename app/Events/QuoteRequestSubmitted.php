<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Order\Domain\Models\QuoteRequest;

class QuoteRequestSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(public QuoteRequest $quoteRequest) {}
}
