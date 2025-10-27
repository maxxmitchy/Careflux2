<?php

namespace Src\Patient\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Medication\Domain\Models\Medication;

class Prescription extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_recurring' => 'boolean',
        'refill_due_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}
