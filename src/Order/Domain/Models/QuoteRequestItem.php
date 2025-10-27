<?php

namespace Src\Order\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Src\Pharmacy\Domain\Models\Pharmacy;

class QuoteRequestItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['negotiated_price' => 'integer'];

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function productable(): MorphTo
    {
        return $this->morphTo();
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }
}
