<?php

namespace Src\Scraping\Application\Actions;

use Illuminate\Support\Collection;
use Src\Scraping\Domain\DTOs\ScrapedProductDTO;
use Src\Scraping\Domain\Models\ScrapedProduct;
use Src\Shared\Domain\Contracts\UpsertActionInterface;

class UpsertScrapedProductsAction implements UpsertActionInterface
{
    public function execute(Collection $dtos): int
    {
        if ($dtos->isEmpty()) {
            return 0;
        }

        // This part is now correct, thanks to the DTO's name mapping.
        $values = $dtos->map(fn (ScrapedProductDTO $dto) => $dto->toArray())->all();

        return ScrapedProduct::upsert(
            $values,
            uniqueBy: ['store_id', 'external_id'],
            update: [
                'product_name',
                'price',
                'image_url',
                'stock_status',
                'search_keyword',
                'product_url',
                'brand',
                'updated_at',
            ]
        );
    }
}
