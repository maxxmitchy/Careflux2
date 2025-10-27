<?php

namespace Src\Shared\Domain\Contracts;

interface DelayStrategyInterface
{
    public function delay(int $seconds): void;
}
