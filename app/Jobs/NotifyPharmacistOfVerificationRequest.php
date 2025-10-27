<?php

namespace App\Jobs;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Src\Pharmacy\Domain\Models\PrescriptionVerification;
use Src\Shared\Infrastructure\Services\TelegramService;

class NotifyPharmacistOfVerificationRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public PrescriptionVerification $verification) {}

    public function handle(TelegramService $telegramService): void
    {
        $verification = $this->verification->load(['patient', 'medicationVariant.medication', 'verifier']);
        $pharmacist = $verification->verifier;
        $url = route('filament.pharmacy.resources.prescription-verifications.edit', ['record' => $verification]);

        // 1. Send Filament Database Notification
        Notification::make()
            ->title('New Prescription Verification')
            ->body("Patient {$verification->patient->full_name} has requested verification for {$verification->medicationVariant->medication->name}.")
            ->warning()
            ->actions([
                Action::make('view')->label('View Request')->url($url),
            ])
            ->sendToDatabase($pharmacist);

        // 2. Send Telegram Notification
        $message = "💊 *New Prescription Verification Request*\n\n".
                   "*Patient:* {$verification->patient->full_name}\n".
                   "*Medication:* {$verification->medicationVariant->medication->name} ({$verification->medicationVariant->name})\n".
                   "*Reference:* `{$verification->reference_code}`\n\n".
                   'Please review and approve this request in your dashboard.';

        $telegramService->sendMessageToUser($pharmacist, $message);
    }
}
