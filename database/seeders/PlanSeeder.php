<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Subscription\Domain\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(
            ['slug' => 'pharmacy-starter-trial'],
            [
                'name' => 'Pharmacist Starter (Trial)',
                'user_type' => 'pharmacy',
                'price_monthly' => 0,
                'description' => 'A 2-month free trial to experience the core features of Careflux.',
                'features' => [
                    'limits' => [
                        'patient_cap' => 50,
                        'tasks_per_month' => 200,
                    ],
                    'display' => [
                        'Up to 50 patients',
                        '200 tasks per month',
                        'Standard support',
                    ],
                ],
                'is_active' => true,
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'personal-community-slot'],
            [
                'name' => 'Additional Personal Community Slot',
                'user_type' => 'user', // This plan is for a User, not a Pharmacy
                'price_monthly' => 500000, // ₦5,000 one-time fee in kobo
                'description' => 'A one-time payment to create an additional personal community.',
                'features' => [],
                'is_active' => true,
            ]
        );
    }
}
