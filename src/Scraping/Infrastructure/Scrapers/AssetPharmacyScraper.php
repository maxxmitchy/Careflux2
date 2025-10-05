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

class AssetPharmacyScraper extends BaseScraper
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
        return 'Asset Pharmacy';
    }

    public function extractProducts(string $html): Collection
    {
        $crawler = new Crawler($html);

        return collect($crawler->filter('div.itemwrap.product')->each(function (Crawler $node) {
            try {
                // Product URL & Name
                $titleNode = $node->filter('div.product-text h3 a');
                $productUrl = $titleNode->count() ? $titleNode->attr('href') : null;
                $name = $titleNode->count() ? trim($titleNode->text('')) : null;

                if (! $productUrl || ! $name) {
                    return null;
                }

                // External ID
                $externalId = $node->filter('a.add_to_wishlist')->count()
                    ? $node->filter('a.add_to_wishlist')->attr('data-product-id')
                    : md5($productUrl);

                // Price
                $priceNode = $node->filter('div.price span.ins, div.price span.mainprice, div.price span.discount')->first();
                $price = $this->extractor->extractPrice($node, 'div.price span.ins, div.price span.mainprice, div.price span.discount');

                // Image
                $image = $this->extractor->firstAttr($node, 'div.product-img a img', ['src', 'data-src', 'data-lazy']);
                if ($image && Str::startsWith($image, '/')) {
                    $image = 'https://assetpharmacy.com'.$image;
                }

                // Stock status → based on class
                $stockStatus = Str::contains($node->attr('class'), 'instock') ? 'In Stock' : 'Out of Stock';

                return new ScrapedProductDTO(
                    storeId: $this->store->id,
                    externalId: $externalId,
                    productName: $name,
                    price: $price,
                    productUrl: $productUrl,
                    imageUrl: $image,
                    stockStatus: $stockStatus,
                    brand: 'Asset Pharmacy'
                );
            } catch (Throwable $e) {
                Log::warning('Failed to parse a product on Asset Pharmacy scrape.', [
                    'store_id' => $this->store->id,
                    'exception' => $e->getMessage(),
                    'html_node' => mb_substr($node->outerHtml(), 0, 800),
                ]);

                return null;
            }
        }))->filter()->unique('externalId');
    }
}
