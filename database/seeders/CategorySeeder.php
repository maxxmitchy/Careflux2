<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding a comprehensive list of medical categories...');

        // Temporarily disable foreign key checks to allow truncation.
        Schema::disableForeignKeyConstraints();

        // Truncate all relevant tables in the correct order.
        DB::table('category_medication')->truncate();
        Category::truncate();

        // Re-enable foreign key checks.
        Schema::enableForeignKeyConstraints();

        $categories = [
            [
                'code' => 'RX',
                'name' => 'Prescription Medications',
                'children' => [
                    ['code' => 'CARDIO', 'name' => 'Cardiovascular'],
                    ['code' => 'DIABETES', 'name' => 'Diabetes'],
                    ['code' => 'ANTIBIO', 'name' => 'Antibiotics'],
                    ['code' => 'ASTHMA', 'name' => 'Asthma & COPD'],
                    ['code' => 'MENTAL', 'name' => 'Mental Health'],
                    ['code' => 'DERM_RX', 'name' => 'Dermatology (Rx)'],
                    ['code' => 'GASTRO_RX', 'name' => 'Gastrointestinal (Rx)'],
                ],
            ],
            [
                'code' => 'OTC',
                'name' => 'Over-the-Counter (OTC)',
                'children' => [
                    ['code' => 'PAIN', 'name' => 'Pain & Fever Relief'],
                    ['code' => 'COLD', 'name' => 'Cough, Cold & Flu'],
                    ['code' => 'ALLERGY', 'name' => 'Allergy & Sinus'],
                    ['code' => 'DIGEST', 'name' => 'Digestive Health & Nausea'],
                    ['code' => 'EYE', 'name' => 'Eye & Ear Care'],
                    ['code' => 'FIRST_AID', 'name' => 'First Aid'],
                ],
            ],
            [
                'code' => 'VMS',
                'name' => 'Vitamins & Supplements',
                'children' => [
                    ['code' => 'MULTI', 'name' => 'Multivitamins'],
                    ['code' => 'MINERALS', 'name' => 'Minerals (Calcium, Iron, Zinc)'],
                    ['code' => 'HERBAL', 'name' => 'Herbal Supplements'],
                    ['code' => 'SPORTS', 'name' => 'Sports Nutrition'],
                    ['code' => 'WEIGHT', 'name' => 'Weight Management'],
                ],
            ],
            [
                'code' => 'PERS_CARE',
                'name' => 'Personal Care',
                'children' => [
                    ['code' => 'SKIN', 'name' => 'Skin Care'],
                    ['code' => 'HAIR', 'name' => 'Hair Care'],
                    ['code' => 'ORAL', 'name' => 'Oral Hygiene'],
                    ['code' => 'FEM', 'name' => 'Feminine Care'],
                ],
            ],
            [
                'code' => 'MOM_BABY',
                'name' => 'Mom & Baby',
                'children' => [
                    ['code' => 'PRENATAL', 'name' => 'Prenatal Vitamins'],
                    ['code' => 'INFANT_F', 'name' => 'Infant Formula'],
                    ['code' => 'DIAPERS', 'name' => 'Diapers & Wipes'],
                    ['code' => 'BABY_CARE', 'name' => 'Baby Skin & Health Care'],
                ],
            ],
            [
                'code' => 'DIAG',
                'name' => 'Diagnostics & Devices',
                'children' => [
                    ['code' => 'BP_MON', 'name' => 'Blood Pressure Monitors'],
                    ['code' => 'GLUCO', 'name' => 'Blood Glucose Monitors'],
                    ['code' => 'THERM', 'name' => 'Thermometers'],
                ],
            ],
            [
                'code' => 'SEXUAL',
                'name' => 'Sexual Wellness',
            ],
        ];

        foreach ($categories as $i => $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $parent = Category::create([
                'category_code' => $categoryData['code'],
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'is_visible' => true,
                'sort_order' => ($i + 1) * 10,
            ]);

            foreach ($children as $j => $childData) {
                Category::create([
                    'parent_id' => $parent->id,
                    'category_code' => $childData['code'],
                    'name' => $childData['name'],
                    'slug' => Str::slug($childData['name']),
                    'is_visible' => true,
                    'sort_order' => ($j + 1) * 10,
                ]);
            }
        }
    }
}
