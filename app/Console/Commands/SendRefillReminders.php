<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Src\Patient\Domain\Models\Prescription;
use App\Jobs\NotifyPharmacistOfRefillJob; // We will create this Job next

class SendRefillReminders extends Command
{
    protected $signature = 'refills:send-reminders {--days= : Specify a single day to check, e.g., --days=7}';
    protected $description = 'Finds recurring prescriptions due for refill and dispatches jobs to create tasks for pharmacists.';

    public function handle(): int
    {
        $this->info('Starting Proactive Refill Reminder Engine...');

        // If a specific day is provided via option, use it. Otherwise, check our standard windows.
        $reminderWindows = $this->option('days') ? [$this->option('days')] : [7, 3, 0];

        foreach ($reminderWindows as $days) {
            $targetDate = Carbon::today()->addDays((int)$days);
            $this->line("Checking for refills due in {$days} days (on {$targetDate->toDateString()})...");

            // Use a chunked query to handle potentially thousands of prescriptions efficiently.
            Prescription::query()
                ->where('is_recurring', true)
                ->whereDate('refill_due_date', $targetDate)
                ->with(['patient.pharmacist']) // Eager-load for performance
                ->chunkById(100, function (Collection $prescriptions) use ($days) {
                    if ($prescriptions->isEmpty()) {
                        return;
                    }

                    // Group by the assigned pharmacist, as they are the ones who will receive the task.
                    $prescriptionsByPharmacist = $prescriptions->groupBy('patient.pharmacist_id');

                    foreach ($prescriptionsByPharmacist as $pharmacistId => $pharmacistPrescriptions) {
                        if (empty($pharmacistId)) {
                            continue; // Skip patients who are not yet assigned to a pharmacist
                        }
                        
                        // Dispatch one job per pharmacist, with all their relevant patient refills for that day.
                        NotifyPharmacistOfRefillJob::dispatch($pharmacistId, $pharmacistPrescriptions, $days);
                    }
                });
        }
        
        $this->info('All refill reminder jobs have been dispatched successfully.');
        return self::SUCCESS;
    }
}