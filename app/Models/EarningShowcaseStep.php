<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EarningShowcaseStep extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'points_example' => 'integer',
        'order' => 'integer',
    ];

    /**
     * The parent showcase this step belongs to.
     */
    public function earningShowcase(): BelongsTo
    {
        return $this->belongsTo(EarningShowcase::class);
    }
}
