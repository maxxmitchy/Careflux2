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

class YouBuyScraper extends BaseScraper
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
        return 'You-Buy';
    }

    public function extractProducts(string $html): Collection
    {
        $crawler = new Crawler($html);

        return collect($crawler->filter('div.listing-product')->each(function (Crawler $node) {
            try {
                // Product name
                $name = $node->filter('input#title')->attr('value')
                    ?? trim($this->extractor->extractText($node, '.product-title'));

                // Product URL
                $href = $this->extractor->firstAttr($node, 'a.product-img', ['href']);
                if (! $href) {
                    return null;
                }
                $productUrl = $this->extractor->resolveUrl($href, 'https://www.u-buy.com.ng');

                // External ID
                $externalId = md5($productUrl.$name);

                // Price (remove <del> old price)
                $priceNode = $node->filter('.product-price')->first();
                $priceHtml = $priceNode->count() ? $priceNode->html() : '';

                // Remove text inside <del>…</del>
                $priceClean = preg_replace('/<del.*?<\/del>/i', '', $priceHtml);
                $price = $this->extractor->extractPriceText(strip_tags($priceClean));

                // Image
                $image = $node->filter('input#image')->attr('value')
                    ?? $this->extractor->firstAttr($node, 'img', ['src', 'data-src', 'data-mainsrc']);
                if ($image && Str::startsWith($image, '//')) {
                    $image = 'https:'.$image;
                }

                // Brand
                $brand = trim($this->extractor->extractText($node, '.brand')) ?: 'You-Buy';

                // Stock
                $stockStatus = 'In Stock';

                // Rating
                $rating = null;
                if ($node->filter('.rating')->count()) {
                    $rating = (float) $node->filter('.rating')->text('');
                }

                if (empty($name) || is_null($price)) {
                    return null;
                }

                // Safety: avoid out-of-range values
                if ($price > 1_000_000_000) {
                    Log::warning('Skipping unrealistic price', [
                        'price' => $price,
                        'product' => $name,
                    ]);

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
                    brand: $brand,
                );
            } catch (Throwable $e) {
                Log::warning('Failed to parse a product on You-Buy scrape.', [
                    'store_id' => $this->store->id,
                    'exception' => $e->getMessage(),
                    'html_node' => mb_substr($node->outerHtml(), 0, 800),
                ]);

                return null;
            }
        }))
            ->filter()               // drop nulls
            ->unique('externalId');  // ensure uniqueness
    }
}
