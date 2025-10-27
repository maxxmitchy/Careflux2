<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Src\Scraping\Application\Actions\UpsertNafdacProductsAction;
use Src\Scraping\Infrastructure\Scrapers\NafdacScraper; // <-- Import the scraper directly

class ScrapeNafdacRegistry extends Command
{
    protected $signature = 'scrape:nafdac-registry {--pages=10 : The number of pages to scrape.}';

    protected $description = 'Scrapes the NAFDAC Green Book to update the local product database.';

    public function handle(NafdacScraper $scraper, UpsertNafdacProductsAction $upsertAction): int
    {
        $this->info('Starting NAFDAC registry scrape...');

        $totalPages = (int) $this->option('pages');
        $totalProductsFound = 0;

        $progressBar = $this->output->createProgressBar($totalPages);
        $progressBar->start();

        for ($page = 1; $page <= $totalPages; $page++) {
            $url = "https://greenbook.nafdac.gov.ng/productCategory/products/{$page}";

            // Directly call our specialized scraper
            $productDTOs = $scraper->scrapeUrl($url);

            if ($productDTOs->isEmpty() && $page > 1) {
                $this->info("\nPage {$page} was empty. Assuming end of results and stopping.");
                break; // Stop if a page has no results
            }

            // Use the dedicated upsert action to save the data
            $upsertAction->execute($productDTOs);
            $totalProductsFound += $productDTOs->count();

            $progressBar->advance();
            sleep(2); // Be polite to the NAFDAC server
        }

        $progressBar->finish();
        $this->info("\nNAFDAC registry scrape complete. Processed {$totalProductsFound} products.");

        return self::SUCCESS;
    }
}
