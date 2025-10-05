<?php

namespace Src\Order\Domain\DTOs;

use Spatie\LaravelData\Data;

class CartItemDTO extends Data
{
    public function __construct(
        public string $cartKey,
        public string $uniqueId,
        public string $type, // 'pharmacy' or 'scraped'
        public string $productName,
        public string $sourceName,
        public ?string $imageUrl,
        public int $price, // in kobo
        public int $quantity,
        public int $pharmacyId,
        public ?int $pharmacistId,
        public bool $isPrescription,
        public ?int $verificationId,
        public ?int $applied_coupon_id = null,
        public ?int $discount_amount = 0,
        public ?int $final_price = null, // price after discount, if any
    ) {}
}
