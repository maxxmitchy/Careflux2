<?php

namespace Src\Pharmacovigilance\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterBatchListEntry extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = false; // This table doesn't need created_at/updated_at

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function masterBatchList(): BelongsTo
    {
        return $this->belongsTo(MasterBatchList::class);
    }
}
