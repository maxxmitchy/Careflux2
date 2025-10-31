<?php

namespace Src\Content\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Medication\Domain\Models\Medication;

class CounselingJourney extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean'];

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(CounselingJourneyStep::class)->orderBy('day_to_send');
    }
}
