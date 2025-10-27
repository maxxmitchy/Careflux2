<?php

namespace Src\Scraping\Infrastructure\Factories;

use Illuminate\Contracts\Container\Container;
use Src\Scraping\Domain\Contracts\ScraperInterface;
use Src\Scraping\Domain\Exceptions\InvalidScraperInterfaceException;
use Src\Scraping\Domain\Exceptions\ScraperClassNotFoundException;
use Src\Scraping\Domain\Exceptions\ScraperNotAssignedException;
use Src\Store\Domain\Models\Store;

final class ScraperFactory
{
    public function __construct(private Container $container) {}

    public function create(Store $store): ScraperInterface
    {
        $scraperClass = $store->scraper_class;

        if (empty($scraperClass)) {
            throw new ScraperNotAssignedException($store);
        }

        if (! class_exists($scraperClass)) {
            throw new ScraperClassNotFoundException($store, $scraperClass);
        }

        $scraper = $this->container->make($scraperClass, ['store' => $store]);

        if (! $scraper instanceof ScraperInterface) {
            throw new InvalidScraperInterfaceException(get_class($scraper), $store);
        }

        return $scraper;
    }
}
