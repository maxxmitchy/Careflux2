<?php

namespace Src\Scraping\Domain\DTOs;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

// --- THIS IS THE DEFINITIVE FIX ---
// This attribute tells the DTO to automatically convert property names
// from camelCase in PHP to snake_case when creating from or transforming to an array.
#[MapName(SnakeCaseMapper::class)]
// --- END OF FIX ---
class ScrapedProductDTO extends Data
{
    public function __construct(
        public string $storeId,
        public string $externalId,
        public string $productName,
        public ?int $price, // in kobo
        public string $productUrl,
        public ?string $imageUrl,
        public ?string $searchKeyword = null,
        public string $stockStatus = 'In Stock',
        public ?string $brand = null
    ) {}
}
