<?php

declare(strict_types=1);

namespace Src\Scraping\Domain\Exceptions;

use Illuminate\Http\Request;
use LogicException;
use Src\Store\Domain\Models\Store;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Thrown when the scraper class name retrieved from the database does not
 * correspond to an actual, existing class in the application.
 *
 * This is a LogicException because it indicates a configuration or deployment
 * error. The class name in the database is incorrect, or the file is missing
 * from the deployed code.
 */
final class ScraperClassNotFoundException extends LogicException
{
    /**
     * The store for which the scraper was being created.
     */
    public readonly Store $store;

    /**
     * The string of the class name that could not be found.
     */
    public readonly string $nonExistentClass;

    /**
     * Create a new exception instance.
     *
     * @param  Store  $store  The store model associated with this attempt.
     * @param  string  $nonExistentClass  The FQCN string that failed the `class_exists()` check.
     * @param  int  $code  The internal exception code.
     * @param  Throwable|null  $previous  The previous throwable used for the exception chaining.
     */
    public function __construct(Store $store, string $nonExistentClass, int $code = 0, ?Throwable $previous = null)
    {
        $this->store = $store;
        $this->nonExistentClass = $nonExistentClass;

        $message = sprintf(
            "The scraper class '%s' configured for store '%s' (ID: %s) could not be found. Please check for typos in the database or ensure the file exists and is correctly namespaced.",
            $nonExistentClass,
            $store->name,
            $store->id
        );

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the structured, context-aware data for logging.
     *
     * This provides invaluable, structured information for debugging configuration errors.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'store_id' => $this->store->id,
            'store_name' => $this->store->name,
            'class_not_found' => $this->nonExistentClass,
            'resolution_hint' => 'Verify the class exists, its namespace is correct, and it is autoloadable. You may need to run `composer dump-autoload`.',
        ];
    }

    /**
     * Render the exception into an HTTP response.
     *
     * Ensures application security by not leaking internal file paths or class names in production.
     */
    public function render(Request $request): \Illuminate\Http\JsonResponse
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        if (app()->isProduction()) {
            return response()->json([
                'message' => 'A server configuration error occurred during request processing.',
            ], $statusCode);
        }

        return response()->json([
            'message' => $this->getMessage(),
            'exception' => self::class,
            'context' => $this->context(),
        ], $statusCode);
    }
}
