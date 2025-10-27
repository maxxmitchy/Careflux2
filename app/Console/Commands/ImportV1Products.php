<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Src\Medication\Domain\Models\Medication;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\Shared\Domain\Models\User;
use ZipArchive;

class ImportV1Products extends Command
{
    protected $signature = 'import:v1-products {--file=v1_export.zip : The path to the v1 export zip file.}';
    protected $description = 'Imports products and images from a v1 export zip file into the v2 database structure.';

    public function handle(): int
    {
        $zipPath = storage_path('app/' . $this->option('file'));

        if (!file_exists($zipPath)) {
            $this->error("❌ Export file not found at: {$zipPath}");
            return self::FAILURE;
        }

        $this->info("📦 Starting v1 product import from {$zipPath}...");

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== TRUE) {
            $this->error("❌ Cannot open zip archive.");
            return self::FAILURE;
        }

        // --- Step 1: Extract JSON ---
        $jsonContent = $zip->getFromName('products.json');
        if ($jsonContent === false) {
            $this->error("❌ products.json not found in the archive.");
            $zip->close();
            return self::FAILURE;
        }

        $products = json_decode($jsonContent, true);
        if (empty($products)) {
            $this->error("❌ products.json is empty or invalid JSON.");
            $zip->close();
            return self::FAILURE;
        }

        // --- Step 2: Extract Files ---
        $this->info("🗂️ Extracting archive contents...");
        $tempDir = storage_path('app/temp_import_images');
        File::deleteDirectory($tempDir);
        File::makeDirectory($tempDir, 0755, true);

        $zip->extractTo($tempDir);
        $zip->close();

        // --- Step 3: Copy images to public storage ---
        $sourceImageDir = $tempDir . '/images';
        $destinationDir = storage_path('app/public/medications');

        if (File::isDirectory($sourceImageDir)) {
            $this->info("🖼️ Copying images to storage/app/public/medications...");
            File::ensureDirectoryExists($destinationDir);

            foreach (File::allFiles($sourceImageDir) as $file) {
                $destPath = $destinationDir . '/' . $file->getFilename();

                // Prevent duplicate copies
                if (!File::exists($destPath)) {
                    File::copy($file->getRealPath(), $destPath);
                }
            }

            $this->info("✅ All images copied successfully.");
        } else {
            $this->warn("⚠️ No 'images' folder found in the archive. Skipping image copy.");
        }

        // --- Step 4: Cache Lookups ---
        $this->info("⚡ Caching existing pharmacies and users...");
        $pharmacies = Pharmacy::all()->keyBy('name');
        $users = User::all()->keyBy('email');
        $adminUser = User::where('is_admin', true)->first();

        if (!$adminUser) {
            $this->error('❌ An admin user is required to attribute created medications. Please create one.');
            File::deleteDirectory($tempDir);
            return self::FAILURE;
        }

        // --- Step 5: Process Products ---
        $this->info("💊 Importing products...");
        $progressBar = $this->output->createProgressBar(count($products));
        $progressBar->start();

        foreach ($products as $productData) {
            DB::transaction(function () use ($productData, $pharmacies, $users, $adminUser) {
                $imagePath = !empty($productData['image_path'])
                    ? 'medications/' . basename($productData['image_path'])
                    : null;

                // Create or update medication
                $medication = Medication::updateOrCreate(
                    ['name' => $productData['medication_name']],
                    [
                        'is_prescription' => $productData['is_prescription'] ?? false,
                        'created_by_user_id' => $adminUser->id,
                        'status' => 'approved',
                        'image' => $imagePath,
                    ]
                );

                // Variant
                $variant = $medication->variants()->firstOrCreate([
                    'name' => $productData['variant_name'],
                ]);

                // Pharmacy + User mapping
                $pharmacy = $pharmacies->get($productData['pharmacy_name']);
                $user = $users->get($productData['user_email']);
                if (!$pharmacy || !$user) {
                    return;
                }

                // Create/update product
                $pharmacy->products()->updateOrCreate(
                    ['medication_variant_id' => $variant->id],
                    [
                        'user_id' => $user->id,
                        'price' => $productData['price_kobo'] ?? 0,
                        'stock' => $productData['stock'] ?? 0,
                        'nafdac_number' => $productData['nafdac_number'] ?? null,
                    ]
                );
            });

            $progressBar->advance();
        }

        $progressBar->finish();

        // --- Step 6: Clean Up ---
        File::deleteDirectory($tempDir);

        $this->info("\n\n✅ Product import completed successfully!");
        $this->warn("💡 Tip: Run `php artisan storage:link` if images aren’t visible publicly.");

        return self::SUCCESS;
    }
}
