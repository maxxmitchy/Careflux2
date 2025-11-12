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

        $followUpTiers = [
            'PATIENT_FOLLOW_UP_48HR' => 2,
            'PATIENT_FOLLOW_UP_7DAY' => 7,
            'PATIENT_FOLLOW_UP_14DAY' => 14,
            'PATIENT_FOLLOW_UP_21DAY' => 21,
        ];

        $taskDefinitions = TaskDefinition::whereIn('key', array_keys($followUpTiers))->get()->keyBy('key');

        // Find all active patients with an assigned pharmacist who HAVE been interacted with before.
        Patient::query()
            ->whereNotNull('pharmacist_id')
            ->whereNotNull('last_interacted_at') // <-- Can also add this at the query level for efficiency
            ->chunkById(100, function ($patients) use ($assignTaskAction, $followUpTiers, $taskDefinitions) {
                foreach ($patients as $patient) {
                   
                    // Guard Clause: If there's no interaction timestamp, we cannot calculate
                    // the duration. Skip this patient for this run.
                    if (is_null($patient->last_interacted_at)) {
                        continue;
                    }

                    $daysSinceInteraction = $patient->last_interacted_at->diffInDays(now());

                    foreach ($followUpTiers as $taskKey => $dayThreshold) {
                        // We check >= to catch anyone who might have been missed on a previous run
                        if ($daysSinceInteraction >= $dayThreshold) {
                            $taskDefinition = $taskDefinitions->get($taskKey);

                            if ($taskDefinition) {
                                $this->info("Assigning {$taskDefinition->name} for patient: {$patient->full_name}");
                                
                                // The execute method returns null if the task already exists, which is perfect.
                                $assignTaskAction->execute(
                                    taskDefinition: $taskDefinition,
                                    assignee: $patient->pharmacist,
                                    subjectable: $patient
                                );
                                
                                
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