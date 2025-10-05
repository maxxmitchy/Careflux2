<?php

namespace App\Observers;

use Src\Scraping\Domain\Models\ScrapedProduct;

class ScrapedProductObserver
{
    public function saving(ScrapedProduct $scrapedProduct): void
    {
        if ($scrapedProduct->isDirty('product_name')) {
            $scrapedProduct->soundex_name = soundex($scrapedProduct->product_name);
        }
    }
}
