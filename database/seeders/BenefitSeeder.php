<?php

namespace Database\Seeders;

use App\Models\Benefit;
use Illuminate\Database\Seeder;

class BenefitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding default landing page benefits...');

        $benefits = [
            ['title' => 'Low Prices, With or Without HMO', 'sort_order' => 10],
            ['title' => 'Automatic Refills, Delivered to Your Door', 'sort_order' => 20],
            ['title' => 'Pharmacists on Call 24/7', 'sort_order' => 30],
            ['title' => 'Accepts Most Insurance Plans', 'sort_order' => 40],
            ['title' => 'Network of Licensed Pharmacists', 'sort_order' => 50],
        ];

        foreach ($benefits as $benefitData) {
            Benefit::updateOrCreate(
                ['title' => $benefitData['title']],
                [
                    'is_active' => true,
                    'sort_order' => $benefitData['sort_order'],
                ]
            );
        }
    }
}
