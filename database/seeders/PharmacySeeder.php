<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Pharmacy\Domain\Models\Pharmacy;

class PharmacySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Pharmacies...');

        $pharmacies = [
            ['name' => 'Medplus Pharmacy - Lekki Phase 1', 'address' => '123 Admiralty Way, Lekki', 'phone' => '08012345671', 'is_approved' => true],
            ['name' => 'HealthPlus Pharmacy - Ikeja', 'address' => '456 Allen Avenue, Ikeja', 'phone' => '08012345672', 'is_approved' => true],
            ['name' => 'Mopheth Pharmacy - Victoria Island', 'address' => '789 Adeola Odeku St, VI', 'phone' => '08012345673', 'is_approved' => true],
            ['name' => 'New Unapproved Pharmacy', 'address' => '101 Unapproved Lane, Surulere', 'phone' => '08012345674', 'is_approved' => false],
        ];

        foreach ($pharmacies as $pharmacyData) {
            Pharmacy::updateOrCreate(['name' => $pharmacyData['name']], $pharmacyData);
        }
    }
}
