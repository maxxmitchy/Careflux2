<?php

namespace Src\Order\Application\Services;

class QuoteRequestService
{
    private const SESSION_KEY_PREFIX = 'quote_requested.';

    public function markAsRequested(string $productUrl): void
    {
        session([self::SESSION_KEY_PREFIX.md5($productUrl) => true]);
    }

    public function unmarkAsRequested(string $productUrl): void
    {
        session()->forget(self::SESSION_KEY_PREFIX.md5($productUrl));
    }

    public function isRecentlyRequested(string $productUrl): bool
    {
        return session()->has(self::SESSION_KEY_PREFIX.md5($productUrl));
    }
}
