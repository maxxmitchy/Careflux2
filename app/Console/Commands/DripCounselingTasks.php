<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Src\Patient\Domain\Models\PatientJourney;

class DripCounselingTasks extends Command
{
    protected $signature = 'drip:queue-counseling-tasks';

    protected $description = 'Finds active patient journeys and queues jobs to create daily counseling tasks.';

    public function handle(): int
    {
        $this->info('Queueing daily counseling tasks...');

        PatientJourney::where('status', 'active')
            ->with(['counselingJourney.steps', 'patient.pharmacist'])
            ->chunkById(100, function ($journeys) {
                foreach ($journeys as $journey) {
                    $daysSinceStart = now()->diffInDays($journey->start_date);
                    $dayToSend = $daysSinceStart + 1; // Day 1 is the first day after purchase

                    $stepForToday = $journey->counselingJourney->steps->firstWhere('day_to_send', $dayToSend);

                    if ($stepForToday) {
                        \App\Jobs\CreateDripCounselingTaskJob::dispatch($journey->patient, $stepForToday); // Corrected Job name
                        $this->line("Dispatching task for Patient #{$journey->patient_id} for Day {$dayToSend}.");
                    }

                    // End the journey if we've passed the last step
                    if ($dayToSend > $journey->counselingJourney->steps->max('day_to_send')) {
                        $journey->update(['status' => 'completed']);
                    }
                }
            });

        $this->info('All jobs dispatched.');

        return self::SUCCESS;
    }
}
