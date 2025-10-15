<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class ProductNafdacMismatch
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  PharmacyProduct  $product  The product that failed verification.
     * @param  string|null  $reason  The specific reason for the failure.
     */
    public function __construct(
        public PharmacyProduct $product,
        public ?string $reason = 'The provided NAFDAC number could not be verified.'
    ) {}
}
