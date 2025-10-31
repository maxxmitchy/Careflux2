<?php

namespace Src\Pharmacovigilance\Checks;

use App\Models\ProductAlert;
use Src\Medication\Domain\Models\Medication;
use Src\Pharmacovigilance\Contracts\PharmacovigilanceCheckInterface;

class BatchNumberVerificationCheck implements PharmacovigilanceCheckInterface
{
    public function run(): void
    {
        // Find medications with known invalid batches
        $medicationsWithInvalidBatches = Medication::whereJsonLength('invalid_batches', '>', 0)->get();

        foreach ($medicationsWithInvalidBatches as $medication) {
            $invalidBatches = $medication->invalid_batches;

            // Find all pharmacy products that stock this medication AND have one of the invalid batches.
            // This is a complex query, a simplified version is shown here.
            $affectedProducts = \Src\Pharmacy\Domain\Models\PharmacyProduct::query()
                ->whereHas('medicationVariant', fn ($q) => $q->where('medication_id', $medication->id))
                ->where(function ($query) use ($invalidBatches) {
                    foreach ($invalidBatches as $batch) {
                        $query->orWhereJsonContains('batch_numbers', $batch);
                    }
                })->exists();

            if ($affectedProducts) {
                // If we find a match, automatically create a ProductAlert.
                // This will then be picked up by our existing alert system.
                ProductAlert::firstOrCreate(
                    [
                        'medication_id' => $medication->id,
                        'type' => 'batch_verification',
                        'title' => "Urgent Batch Verification for {$medication->name}",
                    ],
                    [
                        'created_by_user_id' => 1, // System User
                        'severity' => 'critical',
                        'instructions' => "One or more products in your inventory for {$medication->name} match a known invalid batch number. Please verify all batches immediately and remove any affected stock. Invalid batches: ".implode(', ', $invalidBatches),
                    ]
                );
            }
        }
    }
}
