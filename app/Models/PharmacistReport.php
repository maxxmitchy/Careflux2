<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Shared\Domain\Models\User;

class PharmacistReport extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['week_ending_date' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
