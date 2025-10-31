<?php

namespace Src\Medication\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Patient\Domain\Models\Prescription;
use Src\Shared\Domain\Models\User;

class Medication extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_prescription' => 'boolean',
        'invalid_batches' => 'array',
    ];

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MedicationVariant::class);
    }

    // Add this relationship
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function counselingPoints(): HasMany
    {
        return $this->hasMany(MedicationCounselingPoint::class);
    }
}
