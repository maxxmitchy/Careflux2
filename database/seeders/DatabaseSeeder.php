<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            LocationSeeder::class,
            StoreSeeder::class,
            PlanSeeder::class,
            TaskDefinitionSeeder::class,
            ConversationSeeder::class,
            AnnouncementSeeder::class,
            DeliveryAnimationSeeder::class,
            BenefitSeeder::class,
            FeatureSeeder::class,
            PharmacyShowcaseSeeder::class,
            EarningShowcaseSeeder::class,

            // Hierarchical Seeders (Order is CRITICAL)
            PharmacySeeder::class,
            UserSeeder::class,
            PatientSeeder::class,

            TestimonialSeeder::class,
            SourcingPharmacySeeder::class,

            TrustShowcaseSeeder::class,

            DeliveryZoneSeeder::class,
            DeliveryRateSeeder::class,

            KnowledgeBaseSeeder::class,
        ]);

    }
}
