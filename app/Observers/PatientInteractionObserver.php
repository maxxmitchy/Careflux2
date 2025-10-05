<?php

namespace App\Observers;

use Src\Patient\Domain\Models\PatientInteraction;

class PatientInteractionObserver
{
    /**
     * Handle the PatientInteraction "created" event.
     */
    public function created(PatientInteraction $interaction): void
    {
        // When a new interaction is logged, update the patient's timestamp.
        $interaction->patient()->update(['last_interacted_at' => now()]);
    }
}
