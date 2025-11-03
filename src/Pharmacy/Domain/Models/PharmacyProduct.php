<?php

namespace Src\Pharmacy\Domain\Models;

use App\Models\Category;
use App\Models\MedicationInformation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Src\Gamification\Domain\Models\Task;
use Src\Medication\Domain\Models\MedicationVariant;
use Src\Pharmacy\Domain\Enums\NafdacVerificationStatus;
use Src\Shared\Domain\Models\User;

class PharmacyProduct extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'integer',
        'stock' => 'integer',
        'verification_status' => NafdacVerificationStatus::class,
        'batch_numbers' => 'array',
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
    // public function getNameAttribute(): string
    // {
    //     return $this->medicationVariant->medication->name.' ('.$this->medicationVariant->name.')';
    // }
    /**
     * ACCESSOR: Magically get the product's name from the global catalog.
     * Uses the null-safe operator (?->) to prevent errors if relations are missing.
     */
    public function getNameAttribute(): string
    {
        $medicationName = $this->medicationVariant?->medication?->name ?? 'Archived Medication';
        $variantName = $this->medicationVariant?->name ?? 'N/A';
        return "{$medicationName} ({$variantName})";
    }

    public function getImageAttribute(): ?string
    {
        return $this->medicationVariant->medication?->image;
    }

    public function getIsPrescriptionAttribute(): bool
    {
        return $this->medicationVariant->medication?->is_prescription;
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

    public function tasks(): MorphToMany
    {
        return $this->morphToMany(Task::class, 'subjectable', 'task_subjectables');
    }
}
