<?php

namespace App\Listeners;

use App\Events\ProductNafdacMismatch;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Src\Shared\Infrastructure\Services\TelegramService;
use App\Filament\Pharmacy\Resources\PharmacyProducts\PharmacyProductResource;

class SendMismatchNotification implements ShouldQueue
{
    public function __construct(private TelegramService $telegramService) {}

    public function handle(ProductNafdacMismatch $event): void
    {
        $product = $event->product->load(['user', 'pharmacy']);
        $pharmacist = $product->user;
        $url = PharmacyProductResource::getUrl('edit', ['record' => $product]);

        $title = 'NAFDAC Number Mismatch';
        $body = "The NAFDAC number '{$product->nafdac_number}' for '{$product->name}' could not be verified against the official registry. The product has been flagged.";

        // 1. Send Filament Notification
        Notification::make()
            ->title($title)
            ->body($body)
            ->danger()
            ->actions([\Filament\Actions\Action::make('review')->label('Review Product')->url($url)])
            ->sendToDatabase($pharmacist);

        // 2. Send Telegram Notification
        $message = "🚨 *NAFDAC Number Mismatch Alert*\n\n" .
                   "*Pharmacist:* {$pharmacist->name}\n" .
                   "*Pharmacy:* {$product->pharmacy->name}\n" .
                   "*Product:* {$product->name}\n" .
                   "*Invalid NAFDAC #:* `{$product->nafdac_number}`\n\n" .
                   "This product has been automatically flagged for review.";
        
        $this->telegramService->sendMessageToChannel('admin', $message); // Alert admins
    }
}
