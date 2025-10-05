<?php

namespace Src\Pharmacy\Domain\Models;

use App\Models\Category;
use Src\Shared\Domain\Models\User;
use App\Models\MedicationInformation;
use Illuminate\Database\Eloquent\Model;
use Src\Medication\Domain\Models\MedicationVariant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Src\Pharmacy\Domain\Enums\NafdacVerificationStatus;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PharmacyProduct extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'verification_status' => NafdacVerificationStatus::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function medicationVariant(): BelongsTo
    {
        return $this->belongsTo(MedicationVariant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ACCESSORS to pull data from the global catalog for clean presentation
    public function getNameAttribute(): string
    {
        return $this->medicationVariant->medication->name.' ('.$this->medicationVariant->name.')';
    }

    public function getImageAttribute(): ?string
    {
        return $this->medicationVariant->medication->image;
    }

    public function getIsPrescriptionAttribute(): bool
    {
        return $this->medicationVariant->medication->is_prescription;
    }

    public function medicationInformation(): BelongsToMany
    {
        return $this->belongsToMany(MedicationInformation::class, 'med_info_pharmacy_product');
    }

    /**
     * Many-to-Many with Category.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_pharmacy_product');
    }
}
