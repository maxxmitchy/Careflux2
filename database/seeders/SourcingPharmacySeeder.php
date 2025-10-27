<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Pharmacy\Domain\Models\Pharmacy;

class SourcingPharmacySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a dedicated, unapproved pharmacy to represent internal sourcing.
        Pharmacy::updateOrCreate(
            ['name' => 'Careflux Sourcing'],
            [
                'address' => 'Internal Fulfillment',
                'phone' => config('careflux.default_support_phone'), // Use a config value
                'is_approved' => false, // It's not a public-facing partner
            ]
        );
    }
}
