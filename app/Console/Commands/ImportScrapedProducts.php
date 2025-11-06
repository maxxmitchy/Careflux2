<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Src\Store\Domain\Models\Store;
use Src\Scraping\Domain\Models\ScrapedProduct;

class ImportScrapedProducts extends Command
{
    protected $signature = 'import:scraped-products {file}';

    protected $description = 'Import scraped products from a JSON file, matching stores by name.';

    public function handle(): int
    {
        $file = $this->argument('file');
        $path = storage_path('app/'.$file);

        if (! file_exists($path)) {
            $this->error("Import file not found at: {$path}");

            return self::FAILURE;
        }

        $this->info("Reading products from {$file}...");
        $productsData = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON file.');

            return self::FAILURE;
        }
        if (empty($productsData)) {
            $this->warn('No products found in the file.');

            return self::SUCCESS;
        }

        $this->info('Mapping stores from production database...');
        // 1. Fetch all stores from the production database at once and key them by name for fast lookups.
        $productionStores = Store::all()->keyBy('name');

        $upsertData = [];
        $skippedCount = 0;
        $missingStores = [];

        $this->info('Preparing products for import...');

        foreach ($productsData as $product) {
            // 2. We check for 'store_name', NOT 'store_id'.
            if ($product['store_name'] === null || $product['store_name'] === '') {
                $skippedCount++;

                continue;
            }
            // --- END OF FIX ---

            $storeName = $product['store_name'];
            $store = $productionStores->get($storeName);

            if (! $store) {
                // If the store doesn't exist on production, we skip this product.
                $skippedCount++;
                $missingStores[$storeName] = true; // Track which stores were missing

                continue;
            }

            // 3. Build the final data array for this product, using the CORRECT production store_id.
            $upsertData[] = [
                'id' => (string) Str::ulid(),
                'store_id' => $store->id, // This is the correct production ID
                'external_id' => $product['external_id'],
                'product_name' => $product['product_name'],
                'product_url' => $product['product_url'],
                'image_url' => $product['image_url'],
                'price' => $product['price'],
                'slug' => $product['slug'],
                'original_price' => $product['original_price'],
                'currency' => $product['currency'],
                'stock_status' => $product['stock_status'],
                'brand' => $product['brand'],
                'upc' => $product['upc'],
                'extra' => is_array($product['extra']) ? json_encode($product['extra']) : $product['extra'],
                'search_keyword' => $product['search_keyword'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($upsertData)) {
            $this->error('No valid products to import after filtering.');
            if ($skippedCount > 0) {
                $this->warn("Skipped {$skippedCount} products due to missing store_name or store not found on production.");
                $this->warn('Missing store names: '.implode(', ', array_keys($missingStores)));
            }

            return self::FAILURE;
        }

        $this->info('Upserting '.count($upsertData).' products in bulk...');

        // Use chunking for very large imports to avoid memory issues
        foreach (array_chunk($upsertData, 500) as $chunk) {
            ScrapedProduct::upsert(
                $chunk,
                uniqueBy: ['store_id', 'external_id'],
                update: [
                    'product_name', 'product_url', 'image_url', 'price', 'original_price',
                    'currency', 'stock_status', 'brand', 'upc', 'extra', 'search_keyword', 'updated_at',
                ]
            );
        }

        $this->info('Import complete. Successfully processed '.count($upsertData).' products.');
        if ($skippedCount > 0) {
            $this->warn("Skipped a total of {$skippedCount} products.");
            $this->warn('Missing store names on production: '.implode(', ', array_keys($missingStores)));
        }

        return self::SUCCESS;
    }
}
