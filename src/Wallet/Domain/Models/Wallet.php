<?php

namespace Src\Wallet\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Wallet extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['balance' => 'integer'];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class)->latest();
    }
}
