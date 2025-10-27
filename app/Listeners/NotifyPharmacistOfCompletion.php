<?php

namespace App\Listeners;

use App\Events\QuestionnaireCompleted;
use App\Filament\Pharmacy\Resources\Patients\PatientResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Src\Shared\Infrastructure\Services\TelegramService;

class NotifyPharmacistOfCompletion implements ShouldQueue
{
    public function __construct(private TelegramService $telegramService) {}

    public function handle(QuestionnaireCompleted $event): void
    {
        $invitation = $event->invitation->load(['patient', 'questionnaire', 'sender']);
        $pharmacist = $invitation->sender;
        $patient = $invitation->patient;
        $url = PatientResource::getUrl('view', ['record' => $patient]);

        // Filament Notification
        Notification::make()
            ->title('Questionnaire Completed')
            ->body("{$patient->full_name} has completed the '{$invitation->questionnaire->title}' questionnaire.")
            ->success()->actions([Action::make('view')->label('View Patient Record')->url($url)])
            ->sendToDatabase($pharmacist);

        // Telegram Notification
        $message = "✅ *Questionnaire Completed*\n\n".
                   "*Patient:* {$patient->full_name}\n".
                   "*Questionnaire:* {$invitation->questionnaire->title}\n\n".
                   "The responses are now available to view in the patient's record on your dashboard.";
        $this->telegramService->sendMessageToUser($pharmacist, $message);
    }
}
