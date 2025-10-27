<?php

namespace Src\Product\Application\Actions;

use Illuminate\Support\Collection;
use Src\Product\Domain\Services\ProductSearchService;
use Src\Scraping\Application\Actions\ScrapeByKeywordAction;
use Src\Store\Domain\Models\Store;

class FindAndScrapeProductsAction
{
    private const STALENESS_THRESHOLD_HOURS = 24;

    public function __construct(
        private ProductSearchService $searchService,
        private ScrapeByKeywordAction $scrapeAction
    ) {}

    public function execute(string $keyword, array $selectedStoreIdsToScrape, array $locationFilters, array $otherFilters, ?int $userId): Collection
    {
        // --- THIS IS THE DEFINITIVE FIX ---
        // 1. Extract the 'verifiedOnly' flag at the beginning.
        $verifiedOnly = (bool) ($otherFilters['verifiedOnly'] ?? false);
        // --- END OF FIX ---

        // First, get all results currently in our local database, applying all filters.
        $localResults = $this->searchService->search($keyword, $locationFilters, $otherFilters);

        // --- 2. THE CRITICAL GUARD CLAUSE ---
        // If the user has explicitly requested "Verified Partners Only", or if they are not in a
        // local environment, completely skip the entire live scraping block.
        if ($verifiedOnly || ! app()->environment('local')) {
            return $localResults;
        }
        // --- END OF GUARD CLAUSE ---

        // --- LIVE SCRAPING LOGIC (Now only runs if the above conditions are false) ---
        $storesToScrape = [];
        foreach ($selectedStoreIdsToScrape as $storeId) {
            $productForStore = $localResults->firstWhere('storeId', $storeId);
            $store = Store::find($storeId);

            if ($store && (! $productForStore || $productForStore->updated_at < now()->subHours(self::STALENESS_THRESHOLD_HOURS))) {
                $storesToScrape[] = $storeId;
            }
        }

        if (! empty($storesToScrape)) {
            foreach ($storesToScrape as $storeId) {
                $this->scrapeAction->execute($storeId, $keyword, $userId);
            }

            // After scraping, re-fetch all results to include the new data.
            // This second call will still correctly apply the 'verifiedOnly' filter
            // (which is false at this point), but now it will find the new results.
            return $this->searchService->search($keyword, $locationFilters, $otherFilters);
        }

        // If no scraping was needed, just return the initial local results.
        return $localResults;
    }
}
