<?php

namespace App\Data\Api\V1;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class OrderStoreRequestData extends Data
{
    public function __construct(
        #[Required]
        public CustomerDataDTO $customer,
        #[Required, ArrayType, Min(1)]
        public array $items,
    ) {}

    // We add a custom rule to validate the structure of the items array
    public static function rules(): array
    {
        return [
            'items.*.pharmacy_product_id' => ['required', 'integer', new Exists(PharmacyProduct::class, 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
