<?php

namespace Src\Scraping\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Src\Scraping\Domain\Enums\ScrapeLogStatus;

class ScrapeLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['status' => ScrapeLogStatus::class, 'metadata' => 'array'];

    public ?Collection $scraped_products = null; // In-memory property, not in DB
}
