<?php

namespace src\Pharmacovigilance\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Medication\Domain\Models\Medication;
use Src\Shared\Domain\Models\User;

class ProductAlert extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
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
        'dispatched_at' => 'datetime',
    ];

    /**
     * The global medication record that this alert pertains to.
     */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * The Administrator who initiated this alert.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * The list of specific batch numbers associated with this alert.
     */
    public function batches(): HasMany
    {
        return $this->hasMany(ProductAlertBatch::class);
    }
}
