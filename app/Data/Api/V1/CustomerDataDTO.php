<?php

namespace App\Data\Api\V1;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class CustomerDataDTO extends Data
{
    public function __construct(
        #[Required]
        public string $full_name,
        #[Required, Email]
        public string $email,
        #[Required]
        public string $phone,
        #[Required]
        public string $location_area,
    ) {}
}
