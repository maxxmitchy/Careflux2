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

class UnitedWellnessScraper extends BaseScraper
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
        return 'United Wellness';
    }

    public function extractProducts(string $html): Collection
    {
        $crawler = new Crawler($html);

        return collect($crawler->filter('div.wd-product')->each(function (Crawler $node) {
            try {
                // Product URL
                $href = $this->extractor->firstAttr($node, 'a.product-image-link', ['href']);
                if (! $href) {
                    return null;
                }

                $productUrl = Str::startsWith($href, 'http')
                    ? $href
                    : 'https://unitedwellness.com.ng'.$href;

                $externalId = md5($productUrl);

                // Product name
                $name = $this->extractor->extractText($node, 'h3.wd-entities-title a');

                // Price
                $price = $this->extractor->extractPrice($node, '.wrapp-product-price span.price');

                // Image
                $image = $this->extractor->firstAttr($node, 'img', ['src', 'data-src', 'data-lazy']);
                if ($image && Str::startsWith($image, '/')) {
                    $image = 'https://unitedwellness.com.ng'.$image;
                }

                // Stock
                $stockStatus = str_contains($node->attr('class') ?? '', 'instock')
                    ? 'In Stock'
                    : 'Out of Stock';

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
                    brand: 'UnitedWellness'
                );
            } catch (Throwable $e) {
                Log::warning('Failed to parse a product on UnitedWellness scrape.', [
                    'store_id' => $this->store->id,
                    'exception' => $e->getMessage(),
                    'html_node' => mb_substr($node->outerHtml(), 0, 800),
                ]);

                return null;
            }
        }))->filter()->unique('externalId');
    }
}
