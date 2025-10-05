<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Order\Domain\Models\Invoice;
use Src\Shared\Infrastructure\Services\TelegramService;

class NotifyPharmacistOfNewOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Invoice $invoice)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void
    {
        // Eager-load all necessary relationships for efficiency
        $this->invoice->load(['pharmacy.users', 'patient', 'items']);

        $pharmacy = $this->invoice->pharmacy;
        $patient = $this->invoice->patient;

        // Construct a beautiful, informative message using MarkdownV2
        $message = "🎉 *New Paid Order Received*\n\n".
                   "*Pharmacy:* {$pharmacy->name}\n".
                   "*Order #:* `{$this->invoice->invoice_number}`\n".
                   "*Customer:* {$patient->full_name}\n".
                   '*Total:* ₦'.number_format($this->invoice->total / 100, 2)."\n\n".
                   "*Items:*\n";

        foreach ($this->invoice->items as $item) {
            $message .= "- {$item->description} `(x{$item->quantity})`\n";
        }

        $message .= "\n".
                    'Please confirm this order in your Careflux dashboard as soon as possible.';

        // Find all users in the pharmacy who have a telegram chat ID set
        $recipients = $pharmacy->users()->whereNotNull('telegram_chat_id')->get();

        foreach ($recipients as $recipient) {
            // We need a method in TelegramService to send to a specific user's chat ID
            $telegramService->sendMessageToChatId($recipient->telegram_chat_id, $message);
        }
    }
}
