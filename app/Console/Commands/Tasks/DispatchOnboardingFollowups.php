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
        $this->info('Dispatching 48-hour onboarding follow-up tasks...');

        $taskDefinition = TaskDefinition::where('key', 'PATIENT_FOLLOW_UP_48HR')->first();
        if (! $taskDefinition) {
            $this->error('Task Definition for PATIENT_FOLLOW_UP_48HR not found. Aborting.');
            return self::FAILURE;
        }

        // Find patients created roughly 48 hours ago who have not yet had this specific task created.
        $newPatients = Patient::query()
            ->whereBetween('created_at', [now()->subHours(49), now()->subHours(47)])
            ->whereNotNull('pharmacist_id')
            ->whereDoesntHave('tasks', function ($query) use ($taskDefinition) {
                $query->where('task_definition_id', $taskDefinition->id);
            })
            ->with('pharmacist') // Eager-load the pharmacist
            ->get();

        if ($newPatients->isEmpty()) {
            $this->info('No new patients found needing a 48-hour follow-up.');
            return self::SUCCESS;
        }
        
        $this->info("Found {$newPatients->count()} new patients to process.");

        foreach ($newPatients as $patient) {
            $this->line("- Assigning task for patient: {$patient->full_name} to pharmacist: {$patient->pharmacist->name}");
            
            $assignTaskAction->execute(
                taskDefinition: $taskDefinition,
                assignee: $patient->pharmacist,
                subjectable: $patient,
                
                dueDate: now()->addHours(24) // Task should be completed within the next 24 hours
                
            );
        }

        $this->info('All onboarding follow-up tasks have been assigned.');
        return self::SUCCESS;
    }
}