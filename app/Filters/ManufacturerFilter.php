<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class ManufacturerFilter implements FilterInterface
{
    public function apply(Builder $query, array $params): Builder
    {
        $manufacturer = $params['manufacturer'] ?? null;

        if (! $manufacturer) {

            return $query;
        }

        return $query->where('manufacturer', $manufacturer);
    }
}
