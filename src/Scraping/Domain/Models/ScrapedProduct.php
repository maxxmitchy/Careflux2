<?php

namespace Src\Scraping\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Store\Domain\Models\Store;

class ScrapedProduct extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = ['price' => 'integer'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
