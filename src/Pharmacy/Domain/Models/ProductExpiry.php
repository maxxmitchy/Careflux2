<?php

namespace Src\Pharmacy\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductExpiry extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['expiry_date' => 'date', 'quantity' => 'integer'];

    public function pharmacyProduct(): BelongsTo
    {
        return $this->belongsTo(PharmacyProduct::class);
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by_user_id');
    }
}
