<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Order\Domain\Models\DeliveryZone;

class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding delivery zones...');

        $zones = [
            'Lekki Phase 1',
            'Victoria Island',
            'Ikoyi',
            'Ikeja GRA',
            'Surulere',
            'Yaba',
            'Default', // A fallback for nationwide or un-zoned areas
        ];

        foreach ($zones as $zoneName) {
            DeliveryZone::updateOrCreate(
                ['name' => $zoneName],
                ['is_active' => true]
            );
        }
    }
}
