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

class PurelifeScraper extends BaseScraper
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
        return 'Purelife Pharmacy';
    }

    public function extractProducts(string $html): Collection
    {
        $crawler = new Crawler($html);

        return collect($crawler->filter('.flex.w-full.flex-col.rounded-xl')->each(function (Crawler $node) {
            try {
                // Product URL
                $href = $node->filter('a')->first()->attr('href') ?? '';
                $productUrl = $this->extractor->resolveUrl($href, 'https://purelifepharmacy.odoo.com');

                // External ID
                $externalId = $this->extractExternalIdFromUrl($productUrl);

                // Product name
                $name = trim($node->filter('p.text-sm')->first()->text(''));

                // Price
                $priceText = $node->filter('p.text-sm.font-medium, p.text-lg.font-medium')->first()->text('');
                $price = $this->extractor->extractPriceText($priceText);

                // Image
                $image = $this->extractor->firstAttr($node, 'img', ['src', 'data-src']);
                if ($image && str_starts_with($image, '/')) {
                    $image = rtrim('https://purelifepharmacy.odoo.com', '/').$image;
                }

                // Stock text (e.g., "15 in stock")
                $stockText = $node->filter('span.whitespace-nowrap')->count()
                    ? $node->filter('span.whitespace-nowrap')->text('')
                    : 'In Stock';
                $stockStatus = $this->normalizeStockText($stockText);

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
                    brand: 'Purelife Pharmacy'
                );
            } catch (Throwable $e) {
                Log::warning('Failed to parse Purelife product node.', [
                    'store_id' => $this->store->id,
                    'exception' => $e->getMessage(),
                    'html_node' => mb_substr($node->outerHtml(), 0, 800),
                ]);

                return null;
            }
        }))->filter()->unique('externalId');
    }

    private function extractExternalIdFromUrl(string $url): string
    {
        $parts = parse_url($url);

        if (! empty($parts['path'])) {
            return pathinfo($parts['path'], PATHINFO_FILENAME);
        }

        return md5($url);
    }

    private function normalizeStockText(string $text): string
    {
        $text = strtolower(trim($text));

        if ($text === '' || str_contains($text, 'in stock')) {
            return 'In Stock';
        }

        if (preg_match('/(\d+)\s+left/', $text, $matches)) {
            $qty = (int) $matches[1];

            return $qty > 0 ? 'Limited' : 'Out of Stock';
        }

        if (str_contains($text, 'out of stock')) {
            return 'Out of Stock';
        }

        return 'In Stock';
    }
}
