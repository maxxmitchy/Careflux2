<?php

namespace Src\Scraping\Domain\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Src\Scraping\Domain\Enums\ScrapeLogStatus;
use Src\Scraping\Domain\Models\ScrapeLog;
use Src\Scraping\Infrastructure\Factories\ScraperFactory;
use Src\Shared\ValueObjects\PaginationConfig;
use Src\Store\Domain\Models\Store;
use Throwable;

final readonly class ScraperRunner
{
    public function __construct(
        private ScraperFactory $scraperFactory
    ) {}

    public function runForUrl(Store $store, string $baseUrl, ?string $searchKeyword = null, bool $shouldPaginate = false): ScrapeLog
    {
        $log = ScrapeLog::create([
            'store_id' => $store->id,
            'search_keyword' => $searchKeyword,
            'metadata' => ['base_url' => $baseUrl, 'pagination_enabled' => $shouldPaginate],
        ]);

        try {
            $scraper = $this->scraperFactory->create($store);
            $paginationConfig = $shouldPaginate ? PaginationConfig::fromStore($store) : null;

            if ($paginationConfig) {
                // --- PAGINATION LOGIC ---
                $allProducts = $this->runPaginatedScrape($scraper, $baseUrl, $paginationConfig);
                $log->scraped_products = $allProducts;
                $log->products_found = $allProducts->count();
                $log->status = ScrapeLogStatus::COMPLETED;
            } else {
                // --- SINGLE PAGE LOGIC ---
                $scraper->scrape($log, $baseUrl);
            }

            $log->save();

            return $log->refresh();

        } catch (Throwable $e) {
            Log::error('ScraperRunner failed to execute a scrape.', [
                'store_id' => $store->id, 'error' => $e->getMessage(),
            ]);
            $log->fill(['status' => ScrapeLogStatus::FAILED, 'error_message' => $e->getMessage()])->save();

            return $log;
        }
    }

    private function runPaginatedScrape($scraper, string $baseUrl, PaginationConfig $config): Collection
    {
        $allProducts = new Collection;
        $seenPageHashes = [];

        for ($page = 1; $page <= $config->maxPages; $page++) {
            $paginatedUrl = $config->apply($baseUrl, $page);
            Log::info('Scraping paginated URL', ['url' => $paginatedUrl, 'page' => $page]);

            // Create a temporary, in-memory log for this single page
            $pageLog = new ScrapeLog;
            $scraper->scrape($pageLog, $paginatedUrl);

            $productsOnPage = collect($pageLog->scraped_products ?? []);

            // Stop if the page is empty
            if ($productsOnPage->isEmpty()) {
                Log::info('Empty page encountered, stopping pagination.', ['page' => $page]);
                break;
            }

            // Stop if we see the same content as a previous page
            $pageHash = md5($productsOnPage->pluck('externalId')->sort()->implode(','));
            if (in_array($pageHash, $seenPageHashes)) {
                Log::warning('Duplicate page content detected, stopping pagination to prevent infinite loop.', ['page' => $page]);
                break;
            }
            $seenPageHashes[] = $pageHash;

            $allProducts = $allProducts->merge($productsOnPage);
        }

        return $allProducts;
    }
}
