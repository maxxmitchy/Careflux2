<?php

namespace Src\Pharmacovigilance\Checks;

use App\Models\ProductAlert;
use Illuminate\Support\Facades\DB;
use Src\Pharmacovigilance\Contracts\PharmacovigilanceCheckInterface;
use Src\Pharmacovigilance\Domain\Models\MasterBatchList;

class MasterBatchListCheck implements PharmacovigilanceCheckInterface
{
    public function run(): void
    {
        $activeLists = MasterBatchList::where('status', 'active')->get();

        foreach ($activeLists as $list) {
            $medicationId = $list->medication_id;

            // 1. Get a clean set of all valid batch numbers from the master list.
            // Using pluck and flip creates a hash map for O(1) lookups, which is extremely fast.
            $validBatchMap = DB::table('master_batch_list_entries')
                ->where('master_batch_list_id', $list->id)
                ->pluck('batch_number')
                ->flip();

            if ($validBatchMap->isEmpty()) {
                continue;
            }

            // 2. Find all pharmacy products for this medication that have any batch numbers logged.
            $productsToCheck = \Src\Pharmacy\Domain\Models\PharmacyProduct::query()
                ->whereHas('medicationVariant', fn ($q) => $q->where('medication_id', $medicationId))
                ->whereJsonLength('batch_numbers', '>', 0)
                ->get(['id', 'batch_numbers']);

            $unverifiedProductsExist = false;
            foreach ($productsToCheck as $product) {
                // 3. For each product, check if any of its logged batches are NOT in our valid map.
                $loggedBatches = $product->batch_numbers ?? [];
                foreach ($loggedBatches as $batch) {
                    if (! isset($validBatchMap[$batch])) {
                        $unverifiedProductsExist = true;
                        break 2; // Exit both loops as soon as we find one unverified batch
                    }
                }
            }

            // 4. If we found any product with an unverified batch, create the alert.
            if ($unverifiedProductsExist) {
                ProductAlert::firstOrCreate(
                    [
                        'medication_id' => $medicationId,
                        'type' => 'batch_unverified',
                    ],
                    [
                        'created_by_user_id' => 1, // System User
                        'title' => "Action Required: Unverified Batch Number for {$list->medication->name}",
                        'severity' => 'medium',
                        'instructions' => "One or more products in your inventory for {$list->medication->name} have batch numbers that are not on the manufacturer's master list. Please physically inspect the stock, verify the batch numbers, and correct your inventory log immediately.",
                    ]
                );
            }
        }
    }
}
