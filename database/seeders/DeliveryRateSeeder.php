<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Order\Domain\Models\DeliveryRate;
use Src\Order\Domain\Models\DeliveryZone;
use Src\Pharmacy\Domain\Models\Pharmacy;

class DeliveryRateSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding realistic delivery rates...');

        $pharmacies = Pharmacy::where('is_approved', true)->get();
        $zones = DeliveryZone::where('is_active', true)->get();

        if ($pharmacies->isEmpty() || $zones->isEmpty()) {
            $this->command->warn('Cannot seed delivery rates. Please seed pharmacies and zones first.');

            return;
        }

        // --- Define a realistic pricing matrix (in Kobo) ---
        // This matrix defines the base cost from an ORIGIN zone to a DESTINATION zone.
        $pricingMatrix = [
            'Lekki Phase 1' => [
                'Lekki Phase 1' => 100000, // ₦1,000
                'Victoria Island' => 150000,
                'Ikoyi' => 180000,
                'Surulere' => 250000,
                'Yaba' => 300000,
                'Ikeja GRA' => 350000,
                'Default' => 500000, // ₦5,000 for nationwide
            ],
            'Ikeja GRA' => [
                'Ikeja GRA' => 100000,
                'Yaba' => 150000,
                'Surulere' => 180000,
                'Ikoyi' => 300000,
                'Victoria Island' => 350000,
                'Lekki Phase 1' => 400000,
                'Default' => 500000,
            ],
            // Add more origin zones here...
        ];

        foreach ($pharmacies as $pharmacy) {
            $originZoneName = null;

            // Simple logic to determine the pharmacy's origin zone for the seeder
            foreach ($zones as $zone) {
                if ($pharmacy->address && str_contains(strtolower($pharmacy->address), strtolower($zone->name))) {
                    $originZoneName = $zone->name;
                    break;
                }
            }

            if (! $originZoneName || ! isset($pricingMatrix[$originZoneName])) {
                $this->command->comment("Skipping pharmacy '{$pharmacy->name}' - could not determine a valid origin zone from its address.");

                continue;
            }

            $ratesForOrigin = $pricingMatrix[$originZoneName];

            // Create a rate for every destination zone from this pharmacy
            foreach ($zones as $destinationZone) {
                $baseCost = $ratesForOrigin[$destinationZone->name] ?? 500000; // Default to nationwide price

                // Add a small random variance to make it look more realistic
                $finalCost = $baseCost + (rand(-50, 50) * 100); // +/- ₦50

                DeliveryRate::updateOrCreate(
                    [
                        'pharmacy_id' => $pharmacy->id,
                        'delivery_zone_id' => $destinationZone->id,
                    ],
                    [
                        'cost_kobo' => max(50000, $finalCost), // Ensure price is never below ₦500
                    ]
                );
            }
        }
        $this->command->info('Delivery rates seeded successfully.');
    }
}
