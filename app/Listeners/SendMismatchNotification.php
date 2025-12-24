<?php

namespace App\Listeners;

use App\Events\ProductNafdacMismatch;
use App\Mail\NafdacMismatchMail;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Src\Shared\Infrastructure\Services\TelegramService;

class SendMismatchNotification implements ShouldQueue
{
    public function __construct(private TelegramService $telegramService) {}

    public function handle(ProductNafdacMismatch $event): void
    {
        $product = $event->product->load(['user', 'pharmacy']);
        $pharmacist = $product->user;
        $reason = $event->reason; // <-- Get the reason from the event

        if ($pharmacist->email) {
            Mail::to($pharmacist->email)->send(new NafdacMismatchMail($product, $reason));
        }

        // The URL for the pharmacist to directly edit the problematic product
        $url = route('filament.pharmacy.resources.pharmacy-products.edit', ['record' => $product]);

        // --- 1. Send Filament Notification to the Pharmacist ---
        Notification::make()
            ->title('NAFDAC Verification Failed')
            ->body($reason) // <-- Use the specific reason here
            ->danger()
            ->actions([
                Action::make('review_product')
                    ->label('Review & Correct Product')
                    ->url($url)
                    ->button(),
            ])
            ->sendToDatabase($pharmacist);

        // --- 2. Send High-Priority Telegram Notification to Admins ---
        $message = "🚨 *NAFDAC Verification Failure*\n\n".
                   "*Pharmacist:* {$pharmacist->name}\n".
                   "*Pharmacy:* {$product->pharmacy->name}\n".
                   "*Product:* {$product->name}\n".
                   "*NAFDAC #:* `{$product->nafdac_number}`\n\n".
                   "*Reason:* {$this->escapeReasonForTelegram($reason)}";

        $this->telegramService->sendMessageToChannel('admin', $message);
    }

    /**
     * Helper to clean up the reason for Telegram's Markdown.
     */
    private function escapeReasonForTelegram(string $reason): string
    {
        // Telegram MarkdownV2 is sensitive to many characters.
        // This simple replace is safer than a complex regex for this use case.
        return str_replace("'", '`', $reason);
    }
}
