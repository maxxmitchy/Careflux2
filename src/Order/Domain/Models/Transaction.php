<?php

namespace Src\Order\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Src\Shared\Domain\Models\User;

class Transaction extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $primaryKey = 'reference';

    protected $guarded = [];

    protected $casts = ['amount' => 'integer', 'metadata' => 'array', 'gateway_response' => 'array', 'processed_at' => 'datetime', 'failed_at' => 'datetime'];

    // Relationships: user(), transactionable(), paymentAttempts()

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }
}
