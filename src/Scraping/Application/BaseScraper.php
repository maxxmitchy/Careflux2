<?php

namespace Src\Scraping\Application;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log; // <-- Import Log facade
use Src\Scraping\Domain\Contracts\ScraperInterface;
use Src\Scraping\Domain\DTOs\ScrapedProductDTO;
use Src\Scraping\Domain\Enums\ScrapeLogStatus;
use Src\Scraping\Domain\Models\ScrapeLog;
use Src\Shared\Domain\Contracts\DelayStrategyInterface;
use Src\Shared\Domain\Contracts\UpsertActionInterface;
use Src\Shared\Infrastructure\Services\PuppeteerService;
use Src\Shared\Infrastructure\Services\RetryService;
use Src\Store\Domain\Models\Store;
use Throwable;

abstract class BaseScraper implements ScraperInterface
{
    protected Client $httpClient;

    private const CACHE_DIRECTORY = 'scraped/html';

    public function __construct(
        protected Store $store,
        protected PuppeteerService $puppeteerService,
        protected RetryService $retryService,
        protected UpsertActionInterface $upsertAction,
        protected DelayStrategyInterface $delayStrategy
    ) {
        $this->httpClient = new Client([
            'timeout' => 30,
            'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'],
        ]);
    }

    abstract public function extractProducts(string $html): Collection;

    abstract public static function getDisplayName(): string;

    public function scrape(ScrapeLog $log, string $url): ScrapeLog
    {
        try {
            $html = $this->getHtml($url);

            if (! $html) {
                return $this->failLog($log, 'Failed to fetch HTML after retries.');
            }

            $productDTOs = $this->extractProducts($html);

            if ($log->search_keyword) {
                $productDTOs->each(fn (ScrapedProductDTO $dto) => $dto->searchKeyword = $log->search_keyword);
            }

            $this->upsertAction->execute($productDTOs);

            $log->products_found = $productDTOs->count();
            $log->status = ScrapeLogStatus::COMPLETED;
            $log->save();

            $log->scraped_products = $productDTOs;

            return $log;
        } catch (Throwable $e) {
            // Pass the full exception for better logging
            return $this->failLog($log, $e->getMessage(), $e);
        }
    }

    private function getHtml(string $url): ?string
    {
        $filePath = storage_path('app/'.self::CACHE_DIRECTORY.'/'.md5($url).'.html');

        if (app()->environment('local') && File::exists($filePath)) {
            return File::get($filePath);
        }

        $this->delayStrategy->delay(rand(1, 3));

        $html = $this->fetchHtml($url);

        if ($html && app()->environment('local')) {
            File::ensureDirectoryExists(dirname($filePath));
            File::put($filePath, $html);
        }

        return $html;
    }

    protected function fetchHtml(string $url): ?string
    {
        return $this->retryService->execute(
            fn () => $this->store->requires_javascript
                ? $this->puppeteerService->getHtml($url)
                : (string) $this->httpClient->get($url)->getBody(),
            fn ($e) => $e instanceof ConnectException || ($e instanceof RequestException && $e->getResponse()?->getStatusCode() >= 500)
        );
    }

    /**
     * Fails the log with more detailed context.
     */
    protected function failLog(ScrapeLog $log, string $message, ?Throwable $exception = null): ScrapeLog
    {
        $log->status = ScrapeLogStatus::FAILED;
        $log->error_message = $message;
        $log->save();

        // Log the full exception trace for debugging, but don't save it to the DB record.
        Log::error("Scrape failed for log ID {$log->id}", [
            'store' => $this->store->name,
            'message' => $message,
            'exception' => $exception ? $exception->getTraceAsString() : null,
        ]);

        return $log;
    }
}
