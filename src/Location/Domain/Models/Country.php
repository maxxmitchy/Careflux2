<?php

namespace Src\Location\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $guarded = ['id'];

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }
}
