<?php

namespace Src\Shared\Infrastructure\Support;

use Src\Shared\Domain\Contracts\DelayStrategyInterface;

class SleepDelayStrategy implements DelayStrategyInterface
{
    public function delay(int $seconds): void
    {
        sleep($seconds);
    }
}
