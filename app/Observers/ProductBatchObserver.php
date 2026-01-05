<?php

namespace App\Observers;

use Src\Pharmacy\Domain\Models\ProductBatch;

class ProductBatchObserver
{
    public function saved(ProductBatch $productBatch): void
    {
        $productBatch->pharmacyProduct->syncStockFromBatches();
    }

    public function deleted(ProductBatch $productBatch): void
    {
        $productBatch->pharmacyProduct->syncStockFromBatches();
    }
}
