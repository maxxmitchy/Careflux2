<?php

declare(strict_types=1);

namespace Src\Scraping\Domain\Exceptions;

use Illuminate\Http\Request;
use LogicException;
use Src\Store\Domain\Models\Store;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Thrown when attempting to create a scraper for a Store that does not have a
 * scraper class assigned to it in the database.
 *
 * This extends LogicException as it signifies a data configuration error that
 * must be resolved by an administrator or developer. The application cannot
 * logically proceed without this information.
 */
final class ScraperNotAssignedException extends LogicException
{
    /**
     * The store for which the scraper was being created.
     */
    public readonly Store $store;

    /**
     * Create a new exception instance.
     *
     * @param  Store  $store  The store model that is missing the scraper class.
     * @param  int  $code  The internal exception code.
     * @param  Throwable|null  $previous  The previous throwable used for the exception chaining.
     */
    public function __construct(Store $store, int $code = 0, ?Throwable $previous = null)
    {
        $this->store = $store;

        // Construct a clear, informative message for developers or administrators.
        $message = sprintf(
            "No scraper class has been assigned to the store '%s' (ID: %s). Please update this record in the database.",
            $store->name,
            $store->id
        );

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the structured, context-aware data for logging.
     *
     * This method adds rich, filterable context to your application logs,
     * making it easy to find and fix the problematic data.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'store_id' => $this->store->id,
            'store_name' => $this->store->name,
            'resolution_hint' => "Update the 'scraper_class' column for this store in the 'stores' table.",
        ];
    }

    /**
     * Render the exception into an HTTP response.
     *
     * This provides a safe, generic error in production environments while offering
     * detailed diagnostic information during development.
     */
    public function render(Request $request): \Illuminate\Http\JsonResponse
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        // For production, return a generic error to avoid exposing internal configuration details.
        if (app()->isProduction()) {
            return response()->json([
                'message' => 'A server configuration error occurred while trying to process the request.',
            ], $statusCode);
        }

        // For local/development, provide a detailed response to aid in debugging.
        return response()->json([
            'message' => $this->getMessage(),
            'exception' => self::class,
            'context' => $this->context(),
        ], $statusCode);
    }
}
