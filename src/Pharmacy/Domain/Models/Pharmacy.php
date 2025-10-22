<?php

namespace Src\Pharmacy\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Sanctum\HasApiTokens;
use Src\Location\Domain\Models\City;
use Src\Location\Domain\Models\Country;
use Src\Location\Domain\Models\State;
use Src\Subscription\Domain\Concerns\HasSubscription;

class Pharmacy extends Model
{
    use HasApiTokens;
    use HasFactory;
    use HasSubscription;
    use HasSubscription;

    protected $guarded = ['id'];

    protected $casts = [
        'is_approved' => 'boolean',
        'theme_settings' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function communities(): MorphMany
    {
        return $this->morphMany(Community::class, 'owner');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * The product listings (offers) that belong to this pharmacy.
     */
    public function products(): HasMany
    {
        return $this->hasMany(PharmacyProduct::class);
    }

    protected function fullLocation(): Attribute
    {
        return Attribute::make(
            get: fn () => implode(', ', array_filter([$this->city?->name, $this->state?->name]))
        );
    }
}
