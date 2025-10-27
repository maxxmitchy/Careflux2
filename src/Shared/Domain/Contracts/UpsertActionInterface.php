<?php

namespace Src\Shared\Domain\Contracts;

use Illuminate\Support\Collection;

interface UpsertActionInterface
{
    /**
     * Executes the bulk "upsert" operation for a collection of DTOs.
     *
     * @param  Collection  $dtos  The collection of DTOs to persist.
     * @return int The number of affected rows.
     */
    public function execute(Collection $dtos): int;
}
