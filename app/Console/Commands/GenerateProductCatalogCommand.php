<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Scraping\Domain\Models\ScrapedProduct;

class GenerateProductCatalogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'careflux:generate-product-catalog {--disk=local} {--path=exports/product_catalog.csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a CSV of all products for Google Analytics Data Import.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting product catalog generation...');

        $disk = $this->option('disk');
        $path = $this->option('path');

        // Ensure the directory exists
        Storage::disk($disk)->makeDirectory(dirname($path));
        $filePath = Storage::disk($disk)->path($path);

        try {
            // Use league/csv for robust CSV creation
            $csv = Writer::createFromPath($filePath, 'w+');

            // Define the headers that match our GTM Data Import schema
            $csv->insertOne(['item_id', 'item_category', 'item_brand']);

            // Process Partner Pharmacy Products in chunks
            $this->info('Processing Partner Pharmacy Products...');
            $progressBar = $this->output->createProgressBar(PharmacyProduct::count());
            PharmacyProduct::with(['pharmacy', 'medicationVariant.medication.categories'])->chunk(200, function ($products) use ($csv, $progressBar) {
                foreach ($products as $product) {
                    $csv->insertOne([
                        'item_id' => 'pharmacy::'.$product->id,
                        'item_category' => $product->medicationVariant->medication->categories->first()->name ?? 'Uncategorized',
                        'item_brand' => $product->pharmacy->name,
                    ]);
                    $progressBar->advance();
                }
            });
            $progressBar->finish();
            $this->newLine();

            // Process Scraped Products in chunks
            $this->info('Processing Scraped Products...');
            $progressBar = $this->output->createProgressBar(ScrapedProduct::count());
            ScrapedProduct::with('store')->chunk(200, function ($products) use ($csv, $progressBar) {
                foreach ($products as $product) {
                    $csv->insertOne([
                        'item_id' => 'scraped::'.$product->id,
                        'item_category' => 'General', // Scraped products may not have a category
                        'item_brand' => $product->store->name,
                    ]);
                    $progressBar->advance();
                }
            });
            $progressBar->finish();
            $this->newLine();

            $this->info("✅ Success! Product catalog has been generated at: {$filePath}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ An error occurred during CSV generation: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
