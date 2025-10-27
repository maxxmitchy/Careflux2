<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Shared\Infrastructure\Services\TelegramService;

class SendTelegramMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $chatId,
        public string $message
    ) {}

    public function handle(TelegramService $telegramService): void
    {
        // The job now calls the *private* sending method
        $telegramService->send($this->chatId, $this->message);
    }
}
