<?php

namespace Src\Store\Domain\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Src\Location\Domain\Models\City;
use Src\Location\Domain\Models\Country;
use Src\Location\Domain\Models\State;
use Src\Scraping\Domain\Models\ScrapedProduct;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * Using guarded is safer for a model with many fields.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'priority' => 'boolean',
        'requires_javascript' => 'boolean',
        'page_size' => 'integer',
        'max_retry_attempts' => 'integer',
        'retry_delay_ms' => 'integer',
        'timeout_seconds' => 'integer',
    ];

    /**
     * The country this store is associated with.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * The products that have been scraped from this store.
     */
    public function scrapedProducts(): HasMany
    {
        return $this->hasMany(ScrapedProduct::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    protected function fullLocation(): Attribute
    {
        return Attribute::make(
            get: fn () => implode(', ', array_filter([$this->address, $this->city?->name, $this->state?->name]))
        );
    }
}
