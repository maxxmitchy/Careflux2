<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyShowcaseStep extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['order' => 'integer'];

    public function pharmacyShowcase(): BelongsTo
    {
        return $this->belongsTo(PharmacyShowcase::class);
    }
}
