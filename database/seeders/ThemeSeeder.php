<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding default storefront themes...');

        // Theme 1: The default Careflux green
        Theme::updateOrCreate(
            ['key' => 'modern-green'],
            [
                'name' => 'Modern Green',
                'preview_image_path' => 'themes/previews/modern-green.png', // Placeholder for a screenshot
                'is_active' => true,
            ]
        );

        // Theme 2: A professional, corporate blue alternative
        Theme::updateOrCreate(
            ['key' => 'classic-blue'],
            [
                'name' => 'Classic Blue',
                'preview_image_path' => 'themes/previews/classic-blue.png', // Placeholder for a screenshot
                'is_active' => true,
            ]
        );

        $this->command->info('Seeded 2 storefront themes.');
    }
}
