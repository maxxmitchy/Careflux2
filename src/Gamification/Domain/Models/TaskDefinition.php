<?php

namespace Src\Gamification\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Pharmacy\Domain\Models\Pharmacy;

class TaskDefinition extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_custom' => 'boolean',
        'points' => 'integer',
        'is_active' => 'boolean',
    ];

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }
}
