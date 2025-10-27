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

class MedplusScraper extends BaseScraper
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
        return 'Medplus';
    }

    public function extractProducts(string $html): Collection
    {
        $crawler = new Crawler($html);

        return collect($crawler->filter('div.col-lg-3')->each(function (Crawler $node) {
            try {
                // Product URL (prefer details link, not image)
                $href = $node->filter('div.single-product-details a')->count()
                                ? $node->filter('div.single-product-details a')->attr('href')
                                : null;
                if (! $href) {
                    return null;
                }

                $productUrl = $href;
                $externalId = md5($productUrl);

                // Title
                $name = $this->extractor->extractText($node, 'div.single-product-details a');

                // Price
                $price = $this->extractor->extractPrice($node, 'div.product-price span.text-accent');

                // Image
                $image = $this->extractor->firstAttr($node, 'img', ['src', 'data-src', 'data-lazy']);
                if ($image && Str::startsWith($image, '/')) {
                    $image = 'https://medplusnig.com'.$image;
                }

                // Stock → Medplus mostly shows "Add to cart"
                $stockStatus = 'In Stock';

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
                    brand: 'Medplus'
                );
            } catch (Throwable $e) {
                Log::warning('Failed to parse a product on Medplus scrape.', [
                    'store_id' => $this->store->id,
                    'exception' => $e->getMessage(),
                    'html_node' => mb_substr($node->outerHtml(), 0, 800),
                ]);

                return null;
            }
        }))->filter()->unique('externalId');
    }
}
