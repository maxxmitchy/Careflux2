<?php

namespace Src\Pharmacovigilance\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Medication\Domain\Models\Medication;

class MasterBatchList extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(MasterBatchListEntry::class);
    }
}
