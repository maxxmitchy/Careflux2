<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class MedicationInformation extends Model
{
    protected $table = 'medication_information';

    protected $guarded = ['id'];

    protected $casts = ['is_published' => 'boolean', 'people_also_ask' => 'array'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function pharmacyProducts(): BelongsToMany
    {
        return $this->belongsToMany(PharmacyProduct::class, 'med_info_pharmacy_product');
    }

    public function relatedContent(): BelongsToMany
    {
        return $this->belongsToMany(MedicationInformation::class, 'related_medication_information', 'medication_information_id', 'related_id');
    }

    // --- THIS IS THE DEFINITIVE, MISSING RELATIONSHIP ---
    /**
     * The specific pharmacy products that are advertised or "featured" on this page.
     */
    public function relatedProducts(): BelongsToMany
    {
        // We must specify the pivot table name as it doesn't follow the default convention.
        return $this->belongsToMany(PharmacyProduct::class, 'med_info_related_product');
    }
}
