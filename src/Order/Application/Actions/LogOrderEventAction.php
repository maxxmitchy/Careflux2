<?php

namespace Src\Order\Application\Actions;

use Illuminate\Support\Facades\Auth;
use Src\Order\Domain\Models\Invoice;

class LogOrderEventAction
{
    public function execute(Invoice $invoice, string $eventType, ?array $metadata = null): void
    {
        $invoice->events()->create([
            'user_id' => Auth::id(),
            'event_type' => $eventType,
            'metadata' => $metadata,
        ]);
    }
}
