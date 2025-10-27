<?php

namespace Src\Scraping\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder; // <-- Import the correct Builder
use Illuminate\Support\Facades\DB;
use Src\Scraping\Domain\Models\ScrapedProduct; // <-- Import the model

class ScrapedProductRepository
{
    // ... (existing methods)

    /**
     * Returns an ELOQUENT QUERY BUILDER for grouped products.
     */
    public function getGroupedProductsQuery(): EloquentBuilder
    {
        return ScrapedProduct::query()
            ->select(
                DB::raw('MD5(CONCAT(product_name)) as id'), // unique key for Filament
                'product_name',
                DB::raw('COUNT(DISTINCT store_id) as store_count'),
                DB::raw('MIN(price) as min_price'),
                DB::raw('MAX(price) as max_price')
            )
            ->groupBy('product_name')
            ->orderByDesc('store_count')
            ->orderBy('product_name');
    }
}
