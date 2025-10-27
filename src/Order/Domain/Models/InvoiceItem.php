<?php

namespace Src\Order\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class InvoiceItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['price' => 'integer', 'total' => 'integer', 'quantity' => 'integer'];

    // Relationships: invoice(), pharmacyProduct()

    /**
     * The invoice this item belongs to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * The product from the pharmacy's inventory that this item represents.
     */
    public function pharmacyProduct(): BelongsTo
    {
        return $this->belongsTo(PharmacyProduct::class);
    }
}
