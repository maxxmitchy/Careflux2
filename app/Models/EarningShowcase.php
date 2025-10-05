<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EarningShowcase extends Model
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
        'is_active' => 'boolean',
    ];

    /**
     * The individual animated steps that belong to this showcase.
     * The relationship is ordered to ensure the animation always plays in the correct sequence.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(EarningShowcaseStep::class)->orderBy('order');
    }
}
