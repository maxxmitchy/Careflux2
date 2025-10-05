<?php

namespace Src\Pharmacy\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
    ];

    /**
     * The parent product this variant belongs to.
     */
    public function pharmacyProduct(): BelongsTo
    {
        return $this->belongsTo(PharmacyProduct::class);
    }
}
