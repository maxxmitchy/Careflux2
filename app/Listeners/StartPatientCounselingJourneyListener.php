<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use Src\Content\Domain\Models\CounselingJourney;
use Src\Patient\Domain\Models\PatientJourney;

class StartPatientCounselingJourneyListener
{
    public function handle(OrderCompleted $event): void
    {
        $patient = $event->invoice->patient;
        $pharmacist = $patient->pharmacist;

        // GUARD: Only proceed if both patient and pharmacist have consented.
        if (! $patient->consents_to_daily_drip || ! $pharmacist?->consents_to_daily_drip) {
            return;
        }

        foreach ($event->invoice->items as $item) {
            $medication = $item->pharmacyProduct?->medicationVariant?->medication;
            if (! $medication) {
                continue;
            }

            // Find the active journey for this medication, if one exists.
            $journey = CounselingJourney::where('medication_id', $medication->id)
                ->where('is_active', true)
                ->first();

            if ($journey) {
                // Check if the patient is already on this journey to prevent duplicates.
                $alreadyActive = PatientJourney::where('patient_id', $patient->id)
                    ->where('counseling_journey_id', $journey->id)
                    ->where('status', 'active')
                    ->exists();

                if (! $alreadyActive) {
                    PatientJourney::create([
                        'patient_id' => $patient->id,
                        'counseling_journey_id' => $journey->id,
                        'triggering_invoice_id' => $event->invoice->id,
                        'start_date' => today(),
                    ]);
                }
            }
        }
    }
}
