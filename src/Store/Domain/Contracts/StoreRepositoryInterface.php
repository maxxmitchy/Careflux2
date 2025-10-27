<?php

namespace Src\Store\Domain\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Src\Store\Domain\Models\Store;

interface StoreRepositoryInterface
{
    public function find(string|int $id): ?Store;

    public function getAll(): Collection;
}
