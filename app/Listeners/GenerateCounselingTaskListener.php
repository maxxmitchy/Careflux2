<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Jobs\CreateCounselingTaskJob;

class GenerateCounselingTaskListener
{
    public function handle(OrderCompleted $event): void
    {
        foreach ($event->invoice->items as $item) {
            $medication = $item->pharmacyProduct?->medicationVariant?->medication;
            if (! $medication) {
                continue;
            }

            // INTELLIGENT THROTTLING LOGIC
            // 1. Check if the medication has any critical counseling points.
            $hasCriticalPoints = $medication->counselingPoints()->where('is_critical', true)->exists();

            // 2. Check if this is the first time this patient has received this drug from us.
            $isFirstDispense = ! \Src\Order\Domain\Models\Invoice::query()
                ->where('patient_id', $event->invoice->patient_id)
                ->where('id', '!=', $event->invoice->id) // Exclude the current order
                ->whereHas('items.pharmacyProduct.medicationVariant', fn ($q) => $q->where('medication_id', $medication->id))
                ->exists();

            // 3. If either condition is true, dispatch the job.
            if ($hasCriticalPoints || $isFirstDispense) {
                CreateCounselingTaskJob::dispatch($event->invoice, $medication);
            }
        }
    }
}
