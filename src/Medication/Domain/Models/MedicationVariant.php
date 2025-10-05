<?php

namespace Src\Medication\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class MedicationVariant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * The specific offers from various pharmacies for this exact variant.
     */
    public function pharmacyProducts(): HasMany
    {
        return $this->hasMany(PharmacyProduct::class);
    }
}
