<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding default announcement...');

        Announcement::updateOrCreate(
            ['message' => 'Welcome to the new Careflux! Get 10% off your first order.'],
            [
                'link_text' => 'Shop Now',
                'link_url' => '/products',
                'is_active' => false, // Default to inactive for safety
            ]
        );
    }
}
