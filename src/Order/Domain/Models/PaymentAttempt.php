<?php

namespace Src\Order\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['gateway_response' => 'array'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
