<?php

namespace Database\Seeders;

use App\Models\DeliveryAnimation;
use App\Models\DeliveryAnimationStep;
use Illuminate\Database\Seeder;

class DeliveryAnimationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding default delivery animation...');

        // Ensure no other animations are active
        DeliveryAnimation::where('is_active', true)->update(['is_active' => false]);

        // Create the parent animation record
        $animation = DeliveryAnimation::updateOrCreate(
            ['name' => 'Default Homepage Animation'],
            [
                'headline' => 'Goodbye, pharmacy line.',
                'subheadline' => 'Hello, convenience.',
                'description' => 'Experience hassle-free medication delivery right to your doorstep. Track your order in real-time with our seamless delivery updates.',
                'cta_text' => 'Get Started',
                'cta_url' => '/join',
                'image_path' => 'animations/delivery-package.png', // Assumes an image exists here
                'is_active' => true,
            ]
        );

        // Define the steps for the animation
        $steps = [
            ['order' => 1, 'status_text' => 'Order Confirmed', 'location_text' => 'Careflux Fulfillment Center, Lagos'],
            ['order' => 2, 'status_text' => 'Preparing for Shipment', 'location_text' => 'Packing Station, Lagos'],
            ['order' => 3, 'status_text' => 'In Transit', 'location_text' => 'Regional Distribution Hub, Ikeja'],
            ['order' => 4, 'status_text' => 'Out for Delivery', 'location_text' => 'Your Local Area, Lekki'],
            ['order' => 5, 'status_text' => 'Delivered', 'location_text' => 'Delivered to Customer'],
        ];

        // Create or update each step, associating it with the parent animation
        foreach ($steps as $stepData) {
            DeliveryAnimationStep::updateOrCreate(
                [
                    'delivery_animation_id' => $animation->id,
                    'order' => $stepData['order'],
                ],
                [
                    'status_text' => $stepData['status_text'],
                    'location_text' => $stepData['location_text'],
                ]
            );
        }

        $this->command->comment('Please ensure an image exists at public/storage/animations/delivery-package.png');
    }
}
