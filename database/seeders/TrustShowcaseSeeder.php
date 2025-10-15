<?php

namespace Database\Seeders;

use App\Models\TrustShowcase;
use Illuminate\Database\Seeder;

class TrustShowcaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding default Trust Showcase content...');

        // Ensure no other showcases are active
        TrustShowcase::where('is_active', true)->update(['is_active' => false]);

        // Create the parent showcase record
        $showcase = TrustShowcase::updateOrCreate(
            ['name' => 'Default NAFDAC Trust Showcase'],
            [
                'subheadline' => 'Our Commitment to Safety',
                'headline' => 'Every Product, Verified.',
                'description' => 'We go the extra mile to ensure the authenticity of every medication on our platform. Our system cross-references every product listed by our pharmacy partners with the official NAFDAC registry, providing an unparalleled layer of safety and trust.',
                'is_active' => true,
            ]
        );

        // Clear any old steps to ensure a clean slate
        $showcase->steps()->delete();

        // Create the new, ordered steps for the animation
        $showcase->steps()->createMany([
            [
                'order' => 1,
                'icon' => 'arrow-up-on-square',
                'title' => 'Pharmacist Lists Product',
                'description' => 'A partner pharmacist submits a new product with its NAFDAC number to their inventory.',
            ],
            [
                'order' => 2,
                'icon' => 'magnifying-glass-circle',
                'title' => 'Careflux Engine Verifies',
                'description' => 'Our system instantly cross-references the NAFDAC number with our master database.',
            ],
            [
                'order' => 3,
                'icon' => 'check-badge',
                'title' => 'Product is Verified',
                'description' => 'If the number matches, the product is marked as verified and authentic.',
            ],
            [
                'order' => 4,
                'icon' => 'shield-exclamation',
                'title' => 'Mismatch is Flagged',
                'description' => 'If there is no match, the product is flagged and an alert is sent for review.',
            ],
        ]);
    }
}
