<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Gamification\Domain\Models\TaskDefinition;

class TaskDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        TaskDefinition::updateOrCreate(
            ['key' => 'PATIENT_FOLLOW_UP_REFILL'],
            [
                'name' => 'Patient Refill Reminder Follow-up',
                'description' => 'Points awarded for contacting a patient about an upcoming prescription refill.',
                'points' => 10,
                'is_active' => true,
            ]
        );

        TaskDefinition::updateOrCreate(['key' => 'TECHNICIAN_PRICE_VERIFY'], [
            'name' => 'Verify Product Price', 'points' => 5,
        ]);
        TaskDefinition::updateOrCreate(['key' => 'TECHNICIAN_EXPIRY_LOG'], [
            'name' => 'Log Product Expiry Date', 'points' => 10,
        ]);

        TaskDefinition::updateOrCreate(['key' => 'PHARMACIST_NEW_PRODUCT_VERIFIED'], [
            'name' => 'Verified New Product Listing', 'points' => 10, 'is_active' => true,
        ]);
        TaskDefinition::updateOrCreate(['key' => 'PHARMACIST_PRICE_LEADERSHIP'], [
            'name' => 'Price Leadership', 'points' => 25, 'is_active' => true,
        ]);
        TaskDefinition::updateOrCreate(['key' => 'TECHNICIAN_RESTOCK_ALERT'], [
            'name' => 'Restock Alert Update', 'points' => 10, 'is_active' => true,
        ]);

        TaskDefinition::updateOrCreate(
            ['key' => 'PRODUCT_INTEGRITY_CHECK'],
            [
                'name' => 'Urgent: Product Integrity Check',
                'description' => 'Points awarded for completing a product recall or batch verification task.',
                'points' => 50, // Higher points for higher importance
                'is_active' => true,
            ]
        );

        TaskDefinition::updateOrCreate(
            ['key' => 'PATIENT_COUNSELING_FOLLOW_UP'],
            [
                'name' => 'Patient Counseling Follow-up',
                'description' => 'Points awarded for sending crucial counseling information to a patient about a new or high-risk medication.',
                'points' => 25,
                'is_active' => true,
            ]
        );

        // We can add more tasks here in the future
    }
}
