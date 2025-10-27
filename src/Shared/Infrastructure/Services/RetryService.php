<?php

namespace Src\Shared\Infrastructure\Services;

use Illuminate\Support\Facades\Log;
use LogicException;
use Throwable;

final readonly class RetryService
{
    public function execute(
        callable $callback,
        callable $shouldRetry,
        int $maxAttempts = 3,
        array $backoffMs = [500, 1500]
    ): mixed {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return $callback();
            } catch (Throwable $e) {
                if ($attempt >= $maxAttempts || ! $shouldRetry($e)) {
                    throw $e;
                }

                Log::warning('Operation failed, retrying...', [
                    'attempt' => "$attempt / $maxAttempts",
                    'error' => $e->getMessage(),
                ]);

                usleep(($backoffMs[$attempt - 1] ?? end($backoffMs)) * 1000);
            }
        }
        throw new LogicException('The retry loop completed unexpectedly.');
    }
}
