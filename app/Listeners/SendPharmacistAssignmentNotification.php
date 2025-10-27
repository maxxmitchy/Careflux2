<?php

namespace App\Listeners;

use App\Events\PatientOnboarded;
use App\Mail\PharmacistAssignmentMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPharmacistAssignmentNotification implements ShouldQueue
{
    public function handle(PatientOnboarded $event): void
    {
        $patient = $event->patient->load(['user', 'pharmacist.pharmacy']);

        // Failsafe: Only send if the patient has a user, an email, and an assigned pharmacist.
        if (! $patient->user || ! $patient->user->email || ! $patient->pharmacist) {
            Log::info("Skipping pharmacist assignment email for patient {$patient->id}: Missing user, email, or pharmacist.");

            return;
        }

        Mail::to($patient->user->email)->send(
            new PharmacistAssignmentMail($patient)
        );
    }
}
