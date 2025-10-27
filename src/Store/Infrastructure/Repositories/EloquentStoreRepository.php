<?php

namespace Src\Store\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Src\Store\Domain\Contracts\StoreRepositoryInterface;
use Src\Store\Domain\Models\Store;

class EloquentStoreRepository implements StoreRepositoryInterface
{
    public function find(string|int $id): ?Store
    {
        return Store::find($id);
    }

    public function getAll(): Collection
    {
        return Store::all();
    }
}
