<?php

namespace Src\Order\Application\Services;

use Illuminate\Support\Collection;
use Src\Order\Domain\Models\DeliveryRate;
use Src\Order\Domain\Models\DeliveryZone;

class DeliveryCostService
{
    /**
     * Calculates the total delivery cost for a cart.
     *
     * @param  Collection  $cartItems  A collection of CartItemDTOs.
     * @param  string  $destination  The patient's delivery area/location string.
     * @return int The total delivery cost in kobo.
     */
    public function calculateForCart(Collection $cartItemArrays, string $destination): int
    {
        if ($cartItemArrays->isEmpty() || empty($destination)) {
            return 0;
        }

        // Find which delivery zone the destination falls into.
        // This is a simple implementation; a real-world version might use more complex matching.
        $destinationZone = DeliveryZone::where('is_active', true)
            ->whereRaw('? LIKE CONCAT("%", name, "%")', [$destination])
            ->first();

        if (! $destinationZone) {
            // Fallback to a "Nationwide" or default rate if no specific zone is found.
            $destinationZone = DeliveryZone::where('name', 'Default')->first();
            if (! $destinationZone) {
                return 50000;
            } // Hardcoded fallback of ₦500
        }

        // Get the unique pharmacy IDs from the cart
        $pharmacyIds = $cartItemArrays->pluck('pharmacyId')->unique();

        // Find the rates for these specific origin->destination routes
        $rates = DeliveryRate::whereIn('pharmacy_id', $pharmacyIds)
            ->where('delivery_zone_id', $destinationZone->id)
            ->pluck('cost_kobo', 'pharmacy_id');

        // Sum the cost for each unique pharmacy in the cart
        return $pharmacyIds->reduce(function ($total, $pharmacyId) use ($rates) {
            // If a specific rate exists, use it. Otherwise, use a default.
            return $total + ($rates->get($pharmacyId) ?? 50000); // Default ₦500
        }, 0);
    }
}
