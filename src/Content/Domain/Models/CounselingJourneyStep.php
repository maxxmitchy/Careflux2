<?php

namespace Src\Content\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounselingJourneyStep extends Model
{
    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = ['day_to_send' => 'integer'];

    public function counselingJourney(): BelongsTo
    {
        return $this->belongsTo(CounselingJourney::class);
    }
}
