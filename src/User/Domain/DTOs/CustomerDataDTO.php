<?php

namespace Src\User\Domain\DTOs;

use Spatie\LaravelData\Data;

class CustomerDataDTO extends Data
{
    public function __construct(
        public string $full_name,
        public string $email,
        public string $phone,
        public string $location_area,
    ) {}
}
