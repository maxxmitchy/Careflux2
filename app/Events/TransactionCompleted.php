<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Order\Domain\Models\Transaction;

class TransactionCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Transaction $transaction)
    {
        //
    }
}
