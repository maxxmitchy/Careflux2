<?php

namespace Src\Scraping\Domain\Contracts;

use Illuminate\Support\Collection;

interface HttpScrapingInterface
{
    /**
     * Fetches and parses a single URL.
     */
    public function scrapeUrl(string $url): Collection;
}
