<?php

namespace App\Listeners;

use App\Events\QuoteRequestSubmitted;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Src\Shared\Domain\Models\User;
use Src\Shared\Infrastructure\Services\TelegramService;

class NotifyAssistantsOfNewQuoteRequest implements ShouldQueue
{
    public function __construct(private TelegramService $telegramService) {}

    public function handle(QuoteRequestSubmitted $event): void
    {
        $quoteRequest = $event->quoteRequest;
        $assistants = User::where('is_assistant', true)->get();

        if ($assistants->isEmpty()) {
            return;
        }

        $url = route('filament.pharmacy.resources.quote-requests.view', ['record' => $quoteRequest]);

        Notification::make()
            ->title('New Quote Request Received')
            ->body("A new request from {$quoteRequest->patient_name} requires verification.")
            ->warning()
            ->actions([
                Action::make('view')->label('View Request')->url($url)->button(),
            ])
            ->sendToDatabase($assistants);

        $telegramMessage = "🔔 *New Quote Request*\n\n".
                           "A new request from *{$quoteRequest->patient_name}* needs attention.\n".
                           "[View Request in Panel]({$url})";

        // Assuming you have an 'assistants' channel in your telegram config
        $this->telegramService->sendMessageToChannel('assistants', $telegramMessage);
    }
}
