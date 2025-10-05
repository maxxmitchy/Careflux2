<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Patient\Domain\Models\Patient;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\Shared\Domain\Models\User;

class Coupon extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'discount_amount' => 'integer',
        'expires_at' => 'datetime',
        'redeemed_at' => 'datetime',
    ];
    // Relationships: pharmacy(), creator(), approver(), productable()

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    /**
     * The patient who is eligible to use this coupon.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function productable()
    {
        return $this->morphTo();
    }
}
