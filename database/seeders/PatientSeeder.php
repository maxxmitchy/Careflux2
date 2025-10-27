<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Src\Shared\Domain\Models\User;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Patients...');
        $faker = Faker::create('en_NG');

        $pharmacists = User::where('is_pharmacist', true)->whereNotNull('verified_at')->get();
        if ($pharmacists->isEmpty()) {
            $this->command->error('No verified pharmacists found. Please run UserSeeder first.');

            return;
        }

        for ($i = 0; $i < 25; $i++) {
            $fullName = $faker->name;
            $phone = $faker->unique()->e164PhoneNumber;

            $user = User::firstOrCreate(
                ['phone' => $phone],
                [
                    'name' => $fullName,
                    'email' => $faker->unique()->safeEmail,
                    'password' => Hash::make('password'),
                    'is_patient' => true,
                    'verified_at' => now(),
                ]
            );

            if ($user->patientProfile()->exists()) {
                continue;
            }

            $pharmacist = $pharmacists[$i % $pharmacists->count()];

            // --- THIS IS THE DEFINITIVE FIX ---
            // Ensure the pharmacist has at least one PERSONAL community.
            // The owner of this community will be the User model itself.
            $community = $pharmacist->communities()->first();
            if (! $community) {
                // The communities() relationship on the User model automatically handles setting
                // the `owner_id` and `owner_type` correctly. We no longer pass pharmacy_id.
                $community = $pharmacist->communities()->create([
                    'name' => "{$pharmacist->name}'s Default Community",
                    'description' => "A personal community for patients managed by {$pharmacist->name}.",
                ]);
            }
            // --- END OF FIX ---

            $patient = $user->patientProfile()->create([
                'pharmacist_id' => $pharmacist->id,
                'community_id' => $community->id,
                'full_name' => $fullName,
                'phone' => $phone,
                'date_of_birth' => $faker->dateTimeBetween('-60 years', '-18 years'),
                'gender' => $faker->randomElement(['Male', 'Female']),
                'location_area' => $faker->city,
                'consents_to_contact' => true,
                'last_interacted_at' => now()->subDays(rand(1, 30)),
            ]);
        }
    }
}
