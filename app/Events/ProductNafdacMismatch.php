<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class ProductNafdacMismatch
{
    use Dispatchable, SerializesModels;

    public function __construct(public PharmacyProduct $product)
    {
    }
}