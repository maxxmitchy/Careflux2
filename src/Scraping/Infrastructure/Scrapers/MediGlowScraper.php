<?php

namespace Src\Scraping\Infrastructure\Scrapers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

class MediGlowScraper extends BaseScraper
{
    public function __construct(
        Store $store,
        PuppeteerService $puppeteerService,
        RetryService $retryService,
        UpsertScrapedProductsAction $upsertAction,
        DelayStrategyInterface $delayStrategy,
        private readonly ProductExtractor $extractor
    ) {
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
        return 'MediGlow';
    }

    public function extractProducts(string $html): Collection
    {
        $crawler = new Crawler($html);

        return collect($crawler->filter('section.product')->each(function (Crawler $node) {
            try {
                // Product URL
                $linkNode = $node->filter('a')->first();
                $href = $linkNode->count() ? $linkNode->attr('href') : null;
                if (! $href) {
                    return null;
                }
                $productUrl = $href;

                // External ID from URL
                $externalId = md5($productUrl);

                // Product name
                $name = $this->extractor->extractText($node, 'h3.heading-title a');

                // Price (robust extractor)
                $price = $this->extractor->extractPrice($node, 'span.price');

                // Image (try common attributes)
                $image = $this->extractor->firstAttr($node, 'img', ['src', 'data-src', 'data-lazy-src']);
                if ($image && Str::startsWith($image, '/')) {
                    $image = 'https://mediglow.ng'.$image;
                }

                // Stock status: presence of .out-of-stock span
                $stockStatus = $node->filter('span.out-of-stock')->count() ? 'Out of Stock' : 'In Stock';

                // If essential data missing, skip
                if (empty($name) || is_null($price)) {
                    return null;
                }

                return new ScrapedProductDTO(
                    storeId: $this->store->id,
                    externalId: $externalId,
                    productName: trim($name),
                    price: $price,
                    productUrl: $productUrl,
                    imageUrl: $image,
                    stockStatus: $stockStatus,
                    brand: 'MediGlow'
                );
            } catch (Throwable $e) {
                Log::warning('Failed to parse a product on MediGlow scrape.', [
                    'store_id' => $this->store->id,
                    'exception' => $e->getMessage(),
                    'html_node' => mb_substr($node->outerHtml(), 0, 500),
                ]);

                return null;
            }
        }))->filter()->unique('externalId');
    }
}
