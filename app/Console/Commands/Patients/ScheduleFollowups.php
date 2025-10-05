<?php

namespace App\Console\Commands\Patients;

use Illuminate\Console\Command;
use Src\Gamification\Application\Actions\AssignTaskAction;
use Src\Gamification\Domain\Models\TaskDefinition;
use Src\Patient\Domain\Models\Patient;

class ScheduleFollowups extends Command
{
    protected $signature = 'patients:schedule-followups';

    protected $description = 'Scans for patients needing follow-ups and assigns tasks to pharmacists.';

    public function handle(AssignTaskAction $assignTaskAction): int
    {
        $this->info('Checking for patients who need follow-ups...');

        // Define our follow-up tiers and the corresponding task definition keys
        $followUpTiers = [
            'PATIENT_FOLLOW_UP_48HR' => 2,
            'PATIENT_FOLLOW_UP_7DAY' => 7,
            'PATIENT_FOLLOW_UP_14DAY' => 14,
            'PATIENT_FOLLOW_UP_21DAY' => 21,
        ];

        $taskDefinitions = TaskDefinition::whereIn('key', array_keys($followUpTiers))->get()->keyBy('key');

        // Find all active patients with an assigned pharmacist
        Patient::query()
            ->whereNotNull('pharmacist_id')
            ->whereNotNull('last_interacted_at')
            ->chunkById(100, function ($patients) use ($assignTaskAction, $followUpTiers, $taskDefinitions) {
                foreach ($patients as $patient) {
                    $daysSinceInteraction = $patient->last_interacted_at->diffInDays(now());

                    foreach ($followUpTiers as $taskKey => $dayThreshold) {
                        if ($daysSinceInteraction >= $dayThreshold) {
                            $taskDefinition = $taskDefinitions->get($taskKey);
                            if ($taskDefinition) {
                                $this->info("Assigning {$taskDefinition->name} for patient: {$patient->full_name}");
                                $assignTaskAction->execute(
                                    taskDefinition: $taskDefinition,
                                    assignee: $patient->pharmacist,
                                    subjectable: $patient
                                );
                                // Break after assigning the highest-tier task to avoid spamming
                                break;
                            }
                        }
                    }
                }
            });

        $this->info('Follow-up check complete.');

        return self::SUCCESS;
    }
}
