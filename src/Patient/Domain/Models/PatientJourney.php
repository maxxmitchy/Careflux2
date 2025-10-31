<?php

namespace Src\Patient\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Content\Domain\Models\CounselingJourney;
use Src\Order\Domain\Models\Invoice;

class PatientJourney extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['start_date' => 'date'];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function counselingJourney(): BelongsTo
    {
        return $this->belongsTo(CounselingJourney::class);
    }

    public function triggeringInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'triggering_invoice_id');
    }
}
