<?php

namespace Src\Scraping\Infrastructure\Scrapers;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Src\Scraping\Domain\Contracts\HttpScrapingInterface; // <-- Implement the new interface
use Src\Scraping\Domain\DTOs\NafdacProductDTO;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

class NafdacScraper implements HttpScrapingInterface
{
    private Client $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'],
        ]);
    }

    public function scrapeUrl(string $url): Collection
    {
        try {
            $response = $this->httpClient->get($url);
            $html = (string) $response->getBody();
        } catch (Throwable $e) {
            Log::error("Failed to fetch NAFDAC URL: {$url}", ['error' => $e->getMessage()]);

            return collect(); // Return an empty collection on failure
        }

        return $this->extractProducts($html);
    }

    private function extractProducts(string $html): Collection
    {
        $crawler = new Crawler($html);
        $selector = 'div.col-md-4 div.naf-card';

        return collect($crawler->filter($selector)->each(function (Crawler $node) {
            try {
                $h5 = $node->filter('h5')->html();
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
                    manufacturer: null
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
