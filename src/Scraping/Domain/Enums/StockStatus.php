<?php

namespace Src\Scraping\Domain\Enums;

enum StockStatus: string
{
    /** The product is available for purchase. */
    case IN_STOCK = 'In Stock';

    /** The product is currently unavailable. */
    case OUT_OF_STOCK = 'Out of Stock';

    /** The product is available, but stock levels are low. */
    case LIMITED = 'Limited';
}
