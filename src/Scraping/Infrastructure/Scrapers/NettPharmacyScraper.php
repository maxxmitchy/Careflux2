<?php

namespace Src\Scraping\Infrastructure\Scrapers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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

class NettPharmacyScraper extends BaseScraper
{
    public function __construct(
        Store $store,
        PuppeteerService $puppeteerService,
        RetryService $retryService,
        UpsertScrapedProductsAction $upsertAction,
        DelayStrategyInterface $delayStrategy,
        private readonly ProductExtractor $extractor,
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
        return 'Nett Pharmacy';
    }

    public function extractProducts(string $html): Collection
    {
        $crawler = new Crawler($html);

        // Each product card: <li class="product ...">
        return collect($crawler->filter('li.product')->each(function (Crawler $node) {
            try {
                // Product URL
                $href = $this->extractor->firstAttr($node, 'a.card-title', ['href']);
                $productUrl = $this->extractor->resolveUrl($href, 'https://nettpharmacy.com');

                // External ID
                $externalId = $node->filter('.product-item')->count()
                    ? $node->filter('.product-item')->attr('data-product-id')
                    : md5($productUrl);

                // Title
                $name = trim($node->filter('a.card-title .text')->text(''));

                // Price (e.g., "₦15,500.00")
                $priceText = $node->filter('span.price-item--regular')->text('');
                $price = $this->extractor->extractPriceText($priceText);

                // Image from srcset (take first URL)
                $imageSrcSet = $this->extractor->firstAttr($node, 'img', ['data-srcset', 'srcset']);
                $image = $imageSrcSet ? explode(' ', trim($imageSrcSet))[0] : null;

                // Ensure protocol
                if ($image && str_starts_with($image, '//')) {
                    $image = 'https:'.$image;
                }

                // Stock status
                $stockStatus = $node->filter('span.sold-out-badge')->count() > 0
                    ? 'Out of Stock'
                    : 'In Stock';

                // Validate
                if (empty($name) || is_null($price)) {
                    return null;
                }

                return new ScrapedProductDTO(
                    storeId: $this->store->id,
                    externalId: $externalId,
                    productName: $name,
                    price: $price,
                    productUrl: $productUrl,
                    imageUrl: $image,
                    stockStatus: $stockStatus,
                    brand: 'NettPharmacy'
                );
            } catch (Throwable $e) {
                Log::warning('Failed to parse a product on NettPharmacy scrape.', [
                    'store_id' => $this->store->id,
                    'exception' => $e->getMessage(),
                    'html_node' => mb_substr(method_exists($node, 'outerHtml') ? $node->outerHtml() : $node->html(), 0, 800),
                ]);

                return null;
            }
        }))
            ->filter()
            ->unique('externalId');
    }
}
