<?php

namespace Src\Order\Application\Services;

class QuoteRequestService
{
    private const SESSION_PREFIX = 'quote_requested_';

    private const COOLDOWN_MINUTES = 60;

    /**
     * Mark a product as having been requested using its unique identifier.
     *
     * @param  string|null  $uniqueId  e.g., 'pharmacy::123' or 'scraped::abc'
     */
    public function markAsRequested(?string $uniqueId): void
    {
        if ($uniqueId) {
            session([self::SESSION_PREFIX.$uniqueId => now()]);
        }
    }

    /**
     * Remove a product's "requested" flag from the session.
     */
    public function unmarkAsRequested(?string $uniqueId): void
    {
        if ($uniqueId) {
            session()->forget(self::SESSION_PREFIX.$uniqueId);
        }
    }

    /**
     * Check if a product is currently in the cooldown period.
     */
    public function isRecentlyRequested(?string $uniqueId): bool
    {
        if (! $uniqueId) {
            return false;
        }

        $requestTime = session(self::SESSION_PREFIX.$uniqueId);

        if (! $requestTime) {
            return false;
        }

        return now()->diffInMinutes($requestTime) < self::COOLDOWN_MINUTES;
    }
}
