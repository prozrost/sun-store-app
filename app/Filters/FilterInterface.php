<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

interface FilterInterface
{
    /**
     * Apply filter to the query builder.
     */
    public function apply(Builder $query, array $params): Builder;
}
