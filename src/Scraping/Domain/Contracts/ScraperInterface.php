<?php

namespace Src\Scraping\Domain\Contracts;

use Illuminate\Support\Collection;
use Src\Scraping\Domain\Models\ScrapeLog;

interface ScraperInterface
{
    /**
     * Extracts product data from a given HTML string into a collection of DTOs.
     *
     * @param  string  $html  The raw HTML content to be parsed.
     * @return Collection // Collection of ScrapedProductDTO
     */
    public function extractProducts(string $html): Collection;

    /**
     * Orchestrates the entire scraping process for a single URL.
     *
     * @param  ScrapeLog  $log  The log entry that tracks this operation.
     * @param  string  $url  The final URL to be scraped.
     * @return ScrapeLog The updated ScrapeLog model.
     */
    public function scrape(ScrapeLog $log, string $url): ScrapeLog;

    /**
     * A user-friendly display name for this scraper.
     */
    public static function getDisplayName(): string;
}
