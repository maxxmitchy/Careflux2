<?php

namespace Src\Pharmacy\Domain\Models;

use App\Models\StockClaim;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBatch extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity' => 'integer',
        'cost_price' => 'integer',
    ];

    public function pharmacyProduct(): BelongsTo
    {
        return $this->belongsTo(PharmacyProduct::class);
    }

    /**
     * Scope to find items expiring soon (e.g., within 6 months).
     */
    public function scopeExpiringSoon(Builder $query, int $months = 6): Builder
    {
        return $query->where('quantity', '>', 0)
            ->where('expiry_date', '<=', now()->addMonths($months))
            ->where('expiry_date', '>', now()); // Not already expired
    }

    /**
     * Get the claims made against this batch.
     */
    public function stockClaims(): HasMany
    {
        return $this->hasMany(StockClaim::class, 'product_batch_id');
    }

    /**
     * Calculate how many units are actually available to be claimed.
     * Logic: Total - (Pending + Approved).
     * 'Sold' items should have already decremented the main quantity logic elsewhere.
     */
    public function getAvailableToClaimAttribute(): int
    {
        $reservedQuantity = $this->stockClaims()
            ->whereIn('status', ['pending_approval', 'approved'])
            ->sum('quantity_claimed');

        return max(0, $this->quantity - $reservedQuantity);
    }
}
