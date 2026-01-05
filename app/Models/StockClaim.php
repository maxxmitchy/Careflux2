<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\Pharmacy\Domain\Models\ProductBatch;
use Src\Shared\Domain\Models\User;

class StockClaim extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'approved_at' => 'datetime',
        'agreed_commission_percent' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }

    public function claimer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claiming_user_id');
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'claiming_pharmacy_id');
    }
}
