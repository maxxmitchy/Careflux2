<?php

namespace Src\Order\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Src\Shared\Domain\Models\User;

class Transaction extends Model
{
    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'reference';

    /**
     * The "type" of the primary key ID.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'integer',
        'metadata' => 'array',
        'gateway_response' => 'array',
        'processed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Get the route key for the model. This is critical for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    // protected $guarded = [];

    // protected $casts = ['amount' => 'integer', 'metadata' => 'array', 'gateway_response' => 'array', 'processed_at' => 'datetime', 'failed_at' => 'datetime'];

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
