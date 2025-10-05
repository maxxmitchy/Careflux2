<?php

namespace Src\Subscription\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price_monthly' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
    ];
}
