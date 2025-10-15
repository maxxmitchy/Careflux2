<?php

namespace Src\Scraping\Domain\DTOs;

use Spatie\LaravelData\Data;

class NafdacProductDTO extends Data
{
    public function __construct(
        public string $name,
        public string $nafdac_number,
        public ?string $manufacturer
    ) {}
}
