<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ApprovedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * --- THIS IS THE FIX ---
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  \Illuminate\Database\Eloquent\Builder<TModel>  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     *                                                      --- END OF FIX ---
     */
    public function apply(Builder $builder, Model $model): void
    {
        // This logic is already correct.
        $builder->where($model->getTable().'.status', 'approved');
    }
}
