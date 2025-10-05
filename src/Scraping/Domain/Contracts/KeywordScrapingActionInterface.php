<?php

namespace Src\Scraping\Domain\Contracts;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface KeywordScrapingActionInterface
{
    public function execute(string $storeId, string $keyword, ?int $userId): EloquentCollection;
}
