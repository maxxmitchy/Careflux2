<?php

namespace Src\Store\Domain\Services;

use Src\Store\Domain\Models\Store;

class StoreDelayPolicy
{
    /**
     * Determines the delay in seconds before scraping a store.
     * In the future, this could be based on the store's subscription tier,
     * rate limits, or past performance.
     */
    public function delayFor(Store $store): int
    {
        // Return a random delay between 1 and 3 seconds to be polite.
        return rand(1, 3);
    }
}
