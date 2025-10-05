<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Src\Questionnaire\Domain\Models\QuestionnaireInvitation;
use Src\Shared\Infrastructure\Services\TelegramService; // <-- THE FIX: Import TelegramService
use Throwable;

class SendQuestionnaireInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public QuestionnaireInvitation $invitation)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void // <-- THE FIX: Inject TelegramService
    {
        $this->invitation->load(['patient.user']); // Eager load the user through the patient

        $patient = $this->invitation->patient;
        $user = $patient->user; // The patient's user account

        if (! $user || empty($user->telegram_chat_id)) {
            // Log that we cannot send the notification because the user hasn't linked their Telegram.
            Log::info("Could not send questionnaire invitation {$this->invitation->id} via Telegram: User or telegram_chat_id is missing.");

            return;
        }

        $url = route('questionnaire.show', ['invitation' => $this->invitation->token]);

        // Construct a MarkdownV2-ready message
        $message = "Hello *{$patient->full_name}*,\n\n".
                   "Your pharmacist has sent you a health questionnaire\. Please tap the link below to complete it\.\n\n".
                   "[Open Questionnaire]({$url})";

        try {
            // Use our existing, robust Telegram service to send the message directly to the user.
            $telegramService->sendMessageToUser($user, $message);
        } catch (Throwable $e) {
            Log::error("Failed to send questionnaire invitation via Telegram for invitation ID: {$this->invitation->id}", [
                'error' => $e->getMessage(),
            ]);
            // Optional: Re-throw the exception to have the queue worker retry the job
            $this->fail($e);
        }
    }
}
