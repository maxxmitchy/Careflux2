<?php

namespace Src\Marketing\Domain\DTOs;

use Spatie\LaravelData\Data;

class MarketingAssetData extends Data
{
    public function __construct(
        public string $asset_title,
        public string $type,
        public array $products,
        public ?int $package_price = null // in kobo
    ) {}
}
