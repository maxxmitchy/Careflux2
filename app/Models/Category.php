<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Src\Medication\Domain\Models\Medication;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * The parent category that this category belongs to.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * The child categories that belong to this category.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    // /**
    //  * The pharmacy products that belong to this category.
    //  */
    // public function pharmacyProducts(): BelongsToMany
    // {
    //     return $this->belongsToMany(PharmacyProduct::class, 'category_pharmacy_product');
    // }

    public function medications(): BelongsToMany { return $this->belongsToMany(Medication::class); }
}
