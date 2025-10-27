<?php

namespace Src\Scraping\Infrastructure\Scrapers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Import Str facade
use Src\Scraping\Application\Actions\UpsertScrapedProductsAction;
use Src\Scraping\Application\BaseScraper;
use Src\Scraping\Domain\DTOs\ScrapedProductDTO;
use Src\Scraping\Infrastructure\Services\ProductExtractor;
use Src\Shared\Domain\Contracts\DelayStrategyInterface;
use Src\Shared\Infrastructure\Services\PuppeteerService;
use Src\Shared\Infrastructure\Services\RetryService;
use Src\Store\Domain\Models\Store;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

class BeyondMedplusScraper extends BaseScraper
{
    /**
     * The child constructor accepts all necessary dependencies.
     * It passes the shared dependencies to the parent and only declares
     * its own specific properties.
     */
    public function __construct(
        // Shared dependencies (no visibility/readonly modifiers)
        Store $store,
        PuppeteerService $puppeteerService,
        RetryService $retryService,
        UpsertScrapedProductsAction $upsertAction,
        DelayStrategyInterface $delayStrategy,
        // Scraper-specific dependencies (can have modifiers)
        private readonly ProductExtractor $extractor
    ) {
        // Pass the shared dependencies up to the BaseScraper's constructor.
        parent::__construct(
            $store,
            $puppeteerService,
            $retryService,
            $upsertAction,
            $delayStrategy
        );
    }

    public static function getDisplayName(): string
    {
        return 'Beyond Medplus';
    }

    public function extractProducts(string $html): Collection
    {
        $crawler = new Crawler($html);

        return collect($crawler->filter('div.relative')->each(function (Crawler $node) {
            try {
                // Safely get the href attribute
                $href = $node->filter('a')->first()->count() ? $node->filter('a')->first()->attr('href') : null;
                if (! $href) {
                    return null;
                }

                // For this store, the URL is relative, so we need to prepend the base URL
                $productUrl = 'https://beyondmedplus.com'.$href;

                // Use the URL for a unique external ID
                $externalId = md5($productUrl);

                $name = $this->extractor->extractText($node, 'div.font-avenir a');

                // The price extractor logic needs to be robust
                $price = $this->extractor->extractPrice($node, 'div.font-bold');

                $image = $this->extractor->firstAttr($node, 'img', ['src', 'data-src']);
                // Ensure image URL is absolute
                if ($image && Str::startsWith($image, '/')) {
                    $image = 'https://beyondmedplus.com'.$image;
                }

                // If any essential data is missing, skip this product
                if (empty($name) || is_null($price)) {
                    return null;
                }

                return new ScrapedProductDTO(
                    storeId: $this->store->id,
                    externalId: $externalId,
                    productName: trim($name),
                    price: $price, // price is now in kobo
                    productUrl: $productUrl,
                    imageUrl: $image,
                    stockStatus: 'In Stock', // Assuming 'In Stock' if visible
                    brand: 'Beyond Medplus'
                );
            } catch (Throwable $e) {
                Log::warning('Failed to parse a product on BeyondMedplus scrape.', [
                    'store_id' => $this->store->id,
                    'exception' => $e->getMessage(),
                    'html_node' => mb_substr($node->outerHtml(), 0, 500),
                ]);

                return null;
            }
        }))->filter()->unique('externalId');
    }
}
