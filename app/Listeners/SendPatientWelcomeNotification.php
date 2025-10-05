<?php

namespace App\Listeners;

use App\Events\PatientOnboarded;
use App\Jobs\SendSetPasswordEmailJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password; // We will create this job
use Src\Shared\Infrastructure\Services\TelegramService; // <-- Import Laravel's Password Broker
use Throwable;

class SendPatientWelcomeNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct(private TelegramService $telegramService)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PatientOnboarded $event): void
    {
        try {
            $patient = $event->patient->load('user', 'pharmacist');
            $user = $patient->user;
            $pharmacist = $patient->pharmacist;

            if (! $user || empty($user->telegram_chat_id) || ! $pharmacist) {
                Log::info("Skipping welcome notification for patient {$patient->id}: Missing user, chat ID, or assigned pharmacist.");

                return;
            }

            $token = Password::broker()->createToken($user);

            // 2. Dispatch a job to send the "Set Your Password" email.
            SendSetPasswordEmailJob::dispatch($user, $token);

            $message = "🎉 *Welcome to Careflux, {$patient->full_name}!*\n\n".
                       "You have been successfully onboarded by your personal pharmacist, *{$pharmacist->name}*\.\n\n".
                       "They will be your primary point of contact for medication management, follow-ups, and any health questions you may have\. Expect them to reach out to you shortly to get started\.";

            $this->telegramService->sendMessageToUser($user, $message);

        } catch (Throwable $e) {
            Log::error("Failed to send welcome notification for patient ID: {$event->patient->id}", [
                'error' => $e->getMessage(),
            ]);
            // Re-throw the exception to allow the queue to retry the job
            throw $e;
        }
    }
}
