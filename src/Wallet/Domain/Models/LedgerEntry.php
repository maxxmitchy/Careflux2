<?php

namespace Src\Wallet\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LedgerEntry extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['amount' => 'integer'];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }
}
