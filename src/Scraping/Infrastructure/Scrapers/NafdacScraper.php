<?php

namespace Src\Scraping\Infrastructure\Scrapers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Src\Scraping\Application\BaseScraper;
use Src\Scraping\Domain\DTOs\NafdacProductDTO;
use Src\Shared\Domain\Contracts\UpsertActionInterface; // Import the interface
use Src\Shared\Infrastructure\Services\PuppeteerService;
use Src\Shared\Infrastructure\Services\RetryService;
use Src\Shared\Domain\Contracts\DelayStrategyInterface;
use Src\Store\Domain\Models\Store;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

// This scraper is specialized and does not need to extend BaseScraper
// as it handles a different data type and upsert action.
class NafdacScraper extends BaseScraper
{
    // We override the constructor to accept the specific NAFDAC upsert action
    public function __construct(
        Store $store,
        PuppeteerService $puppeteerService,
        RetryService $retryService,
        // The key difference: it uses its own UpsertAction
        UpsertActionInterface $upsertAction, 
        DelayStrategyInterface $delayStrategy
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
        return 'NAFDAC Green Book';
    }

    public function extractProducts(string $html): Collection
    {
        $crawler = new Crawler($html);

        // Selector based on the provided HTML snippet
        $selector = 'div.col-md-4 div.naf-card';

        return collect($crawler->filter($selector)->each(function (Crawler $node) {
            try {
                // Extracting based on the structure: <h5>, <span>, <span>, <span>
                $h5 = $node->filter('h5')->html(); // Use html() to get content with <span>
                preg_match('/(.*?)<span/s', $h5, $nameMatches);
                $name = trim($nameMatches[1] ?? '');

                $spans = $node->filter('span');
                $nafdacNumberRaw = $spans->last()->text('');
                preg_match('/NRN:\s*(.*)/', $nafdacNumberRaw, $nrnMatches);
                $nafdacNumber = trim($nrnMatches[1] ?? '');

                if (empty($name) || empty($nafdacNumber)) {
                    return null;
                }

                return new NafdacProductDTO(
                    name: $name,
                    nafdac_number: $nafdacNumber,
                    manufacturer: null // The manufacturer is not available in this simple view
                );
            } catch (Throwable $e) {
                Log::warning('Failed to parse a NAFDAC product.', [
                    'exception' => $e->getMessage(),
                    'html_node' => mb_substr($node->outerHtml(), 0, 500),
                ]);
                return null;
            }
        }))->filter();
    }
}