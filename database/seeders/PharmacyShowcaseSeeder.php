<?php

namespace Database\Seeders;

use App\Models\PharmacyShowcase;
use Illuminate\Database\Seeder;

class PharmacyShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        $showcase = PharmacyShowcase::updateOrCreate(['name' => 'Default B2B Showcase'], [
            'headline' => 'Turn Your Pharmacy into a Proactive Growth Engine',
            'subheadline' => 'With the Careflux Task System',
            'description' => 'Don’t wait for patients to walk in. Careflux identifies opportunities such as upcoming refills and turns them into simple tasks for your team. Every completed task drives sales while strengthening patient trust and loyalty.',
            'cta_text' => 'Become a Partner Pharmacy',
            'cta_url' => route('filament.pharmacy.auth.register'),
            'image_path' => 'showcase/pharmacist-dashboard.png', // Placeholder image
            'is_active' => true,
        ]);

        $showcase->steps()->delete(); // Clear old steps
        $showcase->steps()->createMany([
            ['order' => 1, 'icon' => 'bell-alert', 'title' => 'System Identifies Opportunity', 'description' => 'Careflux detects a patient needs a refill in 7 days.'],
            ['order' => 2, 'icon' => 'clipboard-document-list', 'title' => 'Task is Assigned', 'description' => 'A task is automatically sent to your pharmacist\'s dashboard.'],
            ['order' => 3, 'icon' => 'chat-bubble-left-right', 'title' => 'Pharmacist Follows Up', 'description' => 'Your pharmacist sends a proactive WhatsApp reminder to the patient.'],
            ['order' => 4, 'icon' => 'currency-naira', 'title' => 'Sale Confirmed & Patient Retained', 'description' => 'The patient confirms the order, locking in the sale and building loyalty.'],
        ]);
    }
}
