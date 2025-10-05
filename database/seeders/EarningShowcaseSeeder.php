<?php

namespace Database\Seeders;

use App\Models\EarningShowcase;
use Illuminate\Database\Seeder;

class EarningShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        $showcase = EarningShowcase::updateOrCreate(['name' => 'Default Pharmacist Earning Showcase'], [
            'headline' => 'Turn Your Expertise into Earnings',
            'description' => 'Careflux is built on a simple principle: when patients and pharmacists work together, everyone wins. Our intelligent system turns proactive care into rewarding tasks for pharmacists and rewards patients for their commitment to their health journey.',
            'cta_text' => 'Join as a Pharmacist Partner',
            'cta_url' => route('filament.pharmacy.auth.register'),
            'secondary_cta_text' => 'Join as a Customer',
            'secondary_cta_url' => route('register'),
            'image_path' => 'showcase/pharmacist-portrait.png',
            'is_active' => true,
        ]);

        $showcase->steps()->delete();
        $showcase->steps()->createMany([
            ['order' => 1, 'icon' => 'phone-arrow-up-right', 'title' => 'Complete a Follow-up Call', 'description' => 'Check in with a patient 48 hours after they start a new medication.', 'points_example' => 10],
            ['order' => 2, 'icon' => 'clipboard-document-check', 'title' => 'Log a Weekly Report', 'description' => 'Share your insights on patient progress and challenges.', 'points_example' => 25],
            ['order' => 3, 'icon' => 'user-plus', 'title' => 'Onboard a New Patient', 'description' => 'Successfully guide a new user through the Careflux onboarding process.', 'points_example' => 50],
            ['order' => 4, 'icon' => 'arrow-path', 'title' => 'Process a Refill Request', 'description' => 'Ensure a patient receives their recurring medication on time.', 'points_example' => 15],
        ]);
    }
}
