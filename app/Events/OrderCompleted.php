<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Order\Domain\Models\Invoice;

class OrderCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Invoice $invoice) {}
}
