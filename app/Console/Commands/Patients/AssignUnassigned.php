<?php

namespace App\Console\Commands\Patients;

use Illuminate\Console\Command;
use Src\Patient\Application\Actions\AssignPatientToPharmacistAction;
use Src\Patient\Domain\Models\Patient;

class AssignUnassigned extends Command
{
    protected $signature = 'patients:assign-unassigned';

    protected $description = 'Finds and assigns all unassigned patients to the best-fit pharmacist.';

    public function handle(AssignPatientToPharmacistAction $assignAction): int
    {
        $this->info('Searching for unassigned patients...');

        $unassignedPatients = Patient::whereNull('pharmacist_id')->get();

        if ($unassignedPatients->isEmpty()) {
            $this->info('No unassigned patients found.');

            return self::SUCCESS;
        }

        $this->info("Found {$unassignedPatients->count()} patients to assign.");
        $progressBar = $this->output->createProgressBar($unassignedPatients->count());
        $progressBar->start();

        foreach ($unassignedPatients as $patient) {
            try {
                $assignAction->execute($patient);
                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("\nCould not assign patient {$patient->id}: ".$e->getMessage());
            }
        }

        $progressBar->finish();
        $this->info("\nPatient assignment process complete.");

        return self::SUCCESS;
    }
}
