<?php

namespace Src\Pharmacy\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Medication\Domain\Models\MedicationVariant;
use Src\Patient\Domain\Models\Patient;
use Src\Shared\Domain\Models\User;

class PrescriptionVerification extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'quantity_allowed' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function medicationVariant(): BelongsTo
    {
        return $this->belongsTo(MedicationVariant::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }
}
