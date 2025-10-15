<?php

namespace Src\Pharmacy\Domain\DTOs;

use Spatie\LaravelData\Data;
use Src\Pharmacy\Domain\Enums\NafdacVerificationStatus;

class VerificationResultDTO extends Data
{
    public function __construct(
        public NafdacVerificationStatus $status,
        public ?string $reason = null
    ) {}
}
