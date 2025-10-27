<?php

namespace Src\Order\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Shared\Domain\Models\User;

class OrderEvent extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['metadata' => 'array'];

    // Relationships: invoice(), user()

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
