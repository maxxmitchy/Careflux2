<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class ProductUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  PharmacyProduct  $product  The updated product model.
     * @param  array  $originalData  The state of the model before the update.
     */
    public function __construct(
        public PharmacyProduct $product,
        public array $originalData
    ) {}
}
