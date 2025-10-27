<?php

namespace Src\Questionnaire\Application\Actions;

use App\Jobs\SendQuestionnaireInvitationJob;
use Illuminate\Support\Str;
use Src\Patient\Domain\Models\Patient;
use Src\Questionnaire\Domain\Models\Questionnaire;
use Src\Shared\Domain\Models\User; // <-- Import the new job

class SendQuestionnaireAction
{
    public function execute(User $pharmacist, Patient $patient, Questionnaire $questionnaire): void
    {
        if (empty($patient->phone)) {
            // It's good practice to have a pre-check here to avoid creating a record that can't be sent.
            return;
        }

        // Create the unique invitation record
        $invitation = $questionnaire->invitations()->create([
            'token' => Str::random(40),
            'patient_id' => $patient->id,
            'sent_by_user_id' => $pharmacist->id,
        ]);

        // Dispatch the job to the queue for asynchronous sending.
        // The pharmacist's UI will feel instantaneous.
        SendQuestionnaireInvitationJob::dispatch($invitation);
    }
}
