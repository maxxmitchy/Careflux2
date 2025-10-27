<?php

namespace Src\Scraping\Application\Actions;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;
use Src\Scraping\Domain\Contracts\KeywordScrapingActionInterface; // We will create this
use Src\Scraping\Domain\Services\ScraperRunner; // We will create this
use Src\Store\Domain\Contracts\StoreRepositoryInterface;
use Src\Store\Domain\Exceptions\SearchUrlTemplateMissingException;

class ScrapeByKeywordAction implements KeywordScrapingActionInterface
{
    public function __construct(
        private StoreRepositoryInterface $storeRepository,
        private ScraperRunner $scraperRunner
    ) {}

    public function execute(string $storeId, string $keyword, ?int $userId): EloquentCollection
    {
        // For now, we'll keep the logic simple. We can add caching later if needed.
        $store = $this->storeRepository->find($storeId);

        if (empty($store->search_url_template) || ! Str::contains($store->search_url_template, '{query}')) {
            throw new SearchUrlTemplateMissingException("Store '{$store->name}' is not configured for keyword scraping.");
        }

        $searchUrl = str_replace('{query}', urlencode($keyword), $store->search_url_template);

        // The ScraperRunner will handle the actual scraping process
        $scrapeLog = $this->scraperRunner->runForUrl(
            store: $store,
            baseUrl: $searchUrl,
            searchKeyword: $keyword,
            shouldPaginate: true,
        );

        // We assume the ScraperRunner's upsert action has saved the products.
        // We now fetch the results that were just scraped.
        return $store->scrapedProducts()->where('search_keyword', $keyword)->latest()->get();
    }
}
