<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\Shared\Domain\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Users (Admin, Pharmacists, Techs)...');

        $medplus = Pharmacy::where('name', 'Medplus Pharmacy - Lekki Phase 1')->firstOrFail();
        $healthplus = Pharmacy::where('name', 'HealthPlus Pharmacy - Ikeja')->firstOrFail();

        $users = [
            // 1. Super Admin (no pharmacy)
            ['name' => 'Admin User', 'email' => 'admin@careflux.com', 'phone' => '08000000001', 'is_admin' => true, 'verified_at' => now()],

            // 2. Pharmacy Manager at Medplus
            ['name' => 'Chioma Adebayo (Manager)', 'email' => 'manager@medplus.com', 'phone' => '08022223333', 'pharmacy_id' => $medplus->id, 'is_pharmacist' => true, 'is_manager' => true, 'verified_at' => now()],

            // 3. Regular Pharmacist at Medplus
            ['name' => 'Bolanle Salami', 'email' => 'pharmacist@medplus.com', 'phone' => '08044445555', 'pharmacy_id' => $medplus->id, 'is_pharmacist' => true, 'verified_at' => now()],

            // 4. Technician at HealthPlus
            ['name' => 'Tunde Okoro', 'email' => 'tech@healthplus.com', 'phone' => '08066667777', 'pharmacy_id' => $healthplus->id, 'is_technician' => true, 'verified_at' => now()],

            // 5. Unverified Pharmacist
            ['name' => 'Pending Approval', 'email' => 'pending@careflux.com', 'phone' => '08088889999', 'pharmacy_id' => $healthplus->id, 'is_pharmacist' => true, 'verified_at' => null],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['password' => Hash::make('password')])
            );
        }
    }
}
