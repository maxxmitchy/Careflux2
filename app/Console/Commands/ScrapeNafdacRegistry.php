<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Src\Scraping\Domain\Services\ScraperRunner;
use Src\Store\Domain\Models\Store;

class ScrapeNafdacRegistry extends Command
{
    protected $signature = 'scrape:nafdac-registry {--pages=10 : The number of pages to scrape.}';
    protected $description = 'Scrapes the NAFDAC registry to update the local database.';

    public function handle(ScraperRunner $scraperRunner): int
    {
        $this->info('Starting NAFDAC registry scrape...');

        // We create a "virtual" store in memory to represent the NAFDAC site.
        // This allows us to reuse our existing ScraperRunner and BaseScraper architecture.
        $nafdacStore = new Store([
            'name' => 'NAFDAC Registry',
            'scraper_class' => \Src\Scraping\Infrastructure\Scrapers\NafdacScraper::class,
            'requires_javascript' => true, // Assuming it needs JS
        ]);
        
        // This is a HYPOTHETICAL URL and must be replaced with the real one.
        // It must include a placeholder for the page number.
        $baseUrl = 'https://www.nafdac.gov.ng/registered-products/?page={page_number}';
        $totalPages = (int) $this->option('pages');
        $totalProductsFound = 0;

        $progressBar = $this->output->createProgressBar($totalPages);
        $progressBar->start();

        for ($page = 1; $page <= $totalPages; $page++) {
            $url = str_replace('{page_number}', (string) $page, $baseUrl);
            
            // We use our existing, robust ScraperRunner to handle the scrape.
            // Note: We need to adapt the ScraperRunner to use a different UpsertAction for this specific task.
            // For now, we'll assume a modified runner or a direct call.
            
            // This is a simplified call for this example. A real implementation would
            // inject a specific NafdacScraper instance.
            $log = $scraperRunner->runForUrl($nafdacStore, $url);
            
            if ($log->status === \Src\Scraping\Domain\Enums\ScrapeLogStatus::COMPLETED) {
                $totalProductsFound += $log->products_found;
            } else {
                $this->error("Failed to scrape page {$page}. Error: {$log->error_message}");
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nNAFDAC registry scrape complete. Found/updated {$totalProductsFound} products.");

        return self::SUCCESS;
    }
}