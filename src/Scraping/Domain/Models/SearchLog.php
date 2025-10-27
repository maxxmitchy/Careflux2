<?php

namespace Src\Scraping\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Shared\Domain\Models\User;

class SearchLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'result_count' => 'integer',
    ];

    /**
     * The user who performed the search (if they were authenticated).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
