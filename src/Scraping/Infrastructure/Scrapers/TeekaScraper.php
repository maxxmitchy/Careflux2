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

class TeekaScraper extends BaseScraper
{
    /**
     * The constructor accepts dependencies and passes shared ones to the BaseScraper.
     */
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
        return 'Teeka4';
    }

    public function extractProducts(string $html): Collection
    {
        $crawler = new Crawler($html);

        return collect($crawler->filter('li.product')->each(function (Crawler $node) {
            try {
                $href = $node->filter('a.woocommerce-LoopProduct-link')->count()
                    ? $node->filter('a.woocommerce-LoopProduct-link')->attr('href')
                    : null;

                if (! $href) {
                    return null;
                }

                // Make absolute if relative
                $productUrl = $this->extractor->resolveUrl($href, $this->store->search_url_template);

                $externalId = md5($productUrl);

                $name = $this->extractor->extractText($node, 'h2.woocommerce-loop-product__title');
                $priceText = $this->extractor->extractText($node, '.price');
                $price = $this->extractor->extractPriceText($priceText);

                $image = $this->extractor->firstAttr($node, 'img', ['src', 'data-src']);
                if ($image && Str::startsWith($image, '/')) {
                    $image = rtrim($this->store->base_url, '/').$image;
                }

                // Extract promo labels like "Buy 6, Get 3% Off"
                $promos = $node->filter('.awl-label-text .awl-inner-text')->each(
                    fn (Crawler $promoNode) => trim(preg_replace('/\s+/', ' ', $promoNode->text('')))
                );

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
                    stockStatus: 'In Stock',
                    brand: 'Teeka4',
                );
            } catch (Throwable $e) {
                Log::warning('Failed to parse a product on Teeka scrape.', [
                    'store_id' => $this->store->id,
                    'exception' => $e->getMessage(),
                    'html_node' => mb_substr($node->outerHtml(), 0, 500),
                ]);

                return null;
            }
        }))->filter()->unique('externalId');
    }
}
