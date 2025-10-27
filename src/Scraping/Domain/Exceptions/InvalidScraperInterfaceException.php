<?php

declare(strict_types=1);

namespace Src\Scraping\Domain\Exceptions;

use Illuminate\Http\Request;
use LogicException;
use Src\Scraping\Domain\Contracts\ScraperInterface;
use Src\Store\Domain\Models\Store;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Thrown when a class resolved as a scraper does not implement the required ScraperInterface.
 *
 * This is a LogicException because it represents a fundamental error in the program's
 * configuration or code that must be fixed by a developer.
 */
final class InvalidScraperInterfaceException extends LogicException
{
    /**
     * The class that failed the interface check.
     */
    public readonly string $scraperClass;

    /**
     * The store for which the scraper was being created.
     */
    public readonly Store $store;

    /**
     * Create a new exception instance.
     *
     * @param  string  $scraperClass  The fully-qualified class name of the problematic scraper.
     * @param  Store  $store  The store model associated with this attempt.
     * @param  int  $code  The internal exception code.
     * @param  Throwable|null  $previous  The previous throwable used for the exception chaining.
     */
    public function __construct(string $scraperClass, Store $store, int $code = 0, ?Throwable $previous = null)
    {
        $this->scraperClass = $scraperClass;
        $this->store = $store;

        // Construct a clear, informative message for developers.
        $message = sprintf(
            "The scraper class '%s' for store '%s' (ID: %s) must implement the %s interface.",
            $scraperClass,
            $store->name,
            $store->id,
            ScraperInterface::class
        );

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the structured, context-aware data for logging.
     *
     * This method is automatically called by Laravel's exception handler
     * when logging the exception, adding rich context to your logs.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'store_id' => $this->store->id,
            'store_name' => $this->store->name,
            'problematic_class' => $this->scraperClass,
            'required_interface' => ScraperInterface::class,
        ];
    }

    /**
     * Render the exception into an HTTP response.
     *
     * This ensures that if the exception bubbles up to the HTTP layer,
     * it provides a safe, generic error in production but a detailed
     * error during development.
     */
    public function render(Request $request): \Illuminate\Http\JsonResponse
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        // In a production environment, never expose internal class names or implementation details.
        if (app()->isProduction()) {
            return response()->json([
                'message' => 'A server configuration error occurred.',
            ], $statusCode);
        }

        // In non-production environments, provide the detailed error message for easier debugging.
        return response()->json([
            'message' => $this->getMessage(),
            'exception' => self::class,
            'context' => $this->context(),
        ], $statusCode);
    }
}
