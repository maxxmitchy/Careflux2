<?php

namespace App\Console\Commands\Search;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Src\Medication\Domain\Models\Medication;
use Src\Scraping\Domain\Models\ScrapedProduct;

class BackfillSoundexNames extends Command
{
    protected $signature = 'search:backfill-soundex';

    protected $description = 'Populates the soundex_name column for existing medications and scraped products.';

    public function handle(): int
    {
        $this->info('Starting Soundex backfill process...');

        // Backfill Medications
        $this->withProgressBar(Medication::cursor(), function ($medication) {
            DB::table('medications')
                ->where('id', $medication->id)
                ->update(['soundex_name' => soundex($medication->name)]);
        });
        $this->info("\nMedications table backfilled successfully.");

        // Backfill Scraped Products
        $this->withProgressBar(ScrapedProduct::cursor(), function ($product) {
            DB::table('scraped_products')
                ->where('id', $product->id)
                ->update(['soundex_name' => soundex($product->product_name)]);
        });
        $this->info("\nScraped products table backfilled successfully.");

        $this->info('Soundex backfill complete.');

        return self::SUCCESS;
    }
}
