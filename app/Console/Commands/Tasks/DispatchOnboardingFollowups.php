<?php

namespace App\Console\Commands\Tasks;

use Illuminate\Console\Command;
use Src\Gamification\Application\Actions\AssignTaskAction;
use Src\Gamification\Domain\Models\TaskDefinition;
use Src\Patient\Domain\Models\Patient;

class DispatchOnboardingFollowups extends Command
{
    protected $signature = 'tasks:dispatch-onboarding-followups';

    protected $description = 'Find new patients and assign a 48-hour follow-up task to their pharmacist.';

    public function handle(AssignTaskAction $assignTaskAction): int
    {
        $taskDefinition = TaskDefinition::where('key', 'PATIENT_FOLLOW_UP_48HR')->first();
        if (! $taskDefinition) {
            $this->error('Task Definition for PATIENT_FOLLOW_UP_48HR not found. Aborting.');

            return self::FAILURE;
        }

        $newPatients = Patient::where('created_at', '>=', now()->subHours(48))
            ->whereNotNull('pharmacist_id')
            ->doesntHave('tasks') // Ensure we don't assign a duplicate task
            ->get();

        foreach ($newPatients as $patient) {
            $this->info("Assigning 48-hour follow-up for patient: {$patient->full_name}");
            $assignTaskAction->execute(
                taskDefinition: $taskDefinition, // <-- Pass the model
                assignee: $patient->pharmacist,
                subjectable: $patient
            );
        }

        return self::SUCCESS;
    }
}
