<?php

namespace Src\Shared\Infrastructure\Services;

use App\Jobs\SendTelegramMessage;
use Illuminate\Support\Facades\Log;
use Src\Shared\Domain\Models\User;
use Telegram\Bot\Api;
use Throwable;

class TelegramService
{
    protected ?Api $telegram = null;

    protected bool $isConfigured = false;

    public function __construct()
    {
        $token = config('telegram.bots.mybot.token');
        if (! empty($token)) {
            try {
                $this->telegram = new Api($token);
                $this->isConfigured = true;
            } catch (Throwable $e) {
                Log::critical('Telegram SDK failed to initialize.', ['error' => $e->getMessage()]);
                $this->isConfigured = false;
            }
        }
    }

    /**
     * Dispatches a job to send a message to a configured channel.
     */
    public function sendMessageToChannel(string $channelKey, string $message): void
    {
        $chatId = config("careflux.telegram_channels.{$channelKey}");
        SendTelegramMessage::dispatch($chatId, $message);
    }

    /**
     * Dispatches a job to send a message directly to a specific user.
     */
    public function sendMessageToUser(User $user, string $message): void
    {
        SendTelegramMessage::dispatch($user->telegram_chat_id, $message);
    }

    /**
     * This is the core sending logic that is ONLY ever executed by the SendTelegramMessage job.
     * It is now public to be accessible by the job, but should not be called directly from other services.
     */
    public function send(?string $chatId, string $message): void
    {
        if (! $this->isValidChatId($chatId)) {
            Log::error('Telegram Service: Attempted to send a message with an invalid Chat ID format.', ['chat_id' => $chatId]);

            return;
        }

        if (! $this->isConfigured) {
            Log::warning('Telegram Service: Cannot send message because service is not configured.');

            return;
        }

        if (empty($chatId)) {
            Log::info('Telegram Service: Attempted to send message to a destination without a chat ID.');

            return;
        }

        try {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $this->escapeMarkdown($message),
                'parse_mode' => 'MarkdownV2',
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to send Telegram message.', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * --- NEW VALIDATION METHOD ---
     * Validates that a given string is in the format of a Telegram Chat ID (integer, possibly negative).
     */
    private function isValidChatId(?string $chatId): bool
    {
        if (empty($chatId)) {
            return false;
        }

        // A valid ID is a numeric string, which may start with a hyphen.
        return (bool) preg_match('/^-?[0-9]+$/', $chatId);
    }

    private function escapeMarkdown(string $text): string
    {
        $charactersToEscape = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
        foreach ($charactersToEscape as $char) {
            $text = str_replace($char, '\\'.$char, $text);
        }

        return $text;
    }
}
