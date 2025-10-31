<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Content\Domain\Models\CounselingJourney;
use Src\Medication\Domain\Models\Medication;

class CounselingJourneySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding default counseling journey...');
        $metformin = Medication::where('name', 'like', '%Metformin%')->first();
        if ($metformin) {
            $journey = CounselingJourney::updateOrCreate(
                ['medication_id' => $metformin->id],
                ['name' => '7-Day Metformin Onboarding', 'description' => 'A daily check-in for patients starting Metformin.', 'is_active' => true]
            );
            $journey->steps()->delete();
            $journey->steps()->createMany([
                ['day_to_send' => 1, 'message_template' => 'Hi {patient_name}, just checking in on your first day of taking {medication_name}. Remember to take it with a meal to reduce stomach upset.'],
                ['day_to_send' => 2, 'message_template' => 'Hi {patient_name}, how did you feel after your dose of {medication_name} yesterday? Any mild nausea or stomach discomfort is common and usually passes.'],
                ['day_to_send' => 7, 'message_template' => 'Hi {patient_name}, it\'s been a week on {medication_name}! How are you feeling? Remember to keep monitoring your blood sugar as your doctor advised.'],
            ]);
        }
    }
}
