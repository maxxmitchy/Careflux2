<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding default landing page features...');

        $features = [
            [
                'order' => 10,
                'icon' => 'chat-bubble-left-right',
                'title' => 'Consistent Follow-ups',
                'description' => 'Receive personalized check-ins from your dedicated pharmacist to track your progress and ensure your treatment is effective.',
            ],
            [
                'order' => 20,
                'icon' => 'phone-arrow-up-right',
                'title' => 'Expert Advice On-Demand',
                'description' => 'Your personal pharmacist is just a call or message away, ready to answer your questions and provide clarity on your health journey.',
            ],
            [
                'order' => 30,
                'icon' => 'shield-check',
                'title' => 'Trusted & Verified Network',
                'description' => 'Our platform connects you exclusively with licensed, partner pharmacists. You can instantly confirm your caregiver\'s credentials for peace of mind.',
            ],
        ];

        foreach ($features as $featureData) {
            Feature::updateOrCreate(
                ['title' => $featureData['title']],
                [
                    'icon' => $featureData['icon'],
                    'description' => $featureData['description'],
                    'sort_order' => $featureData['order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
