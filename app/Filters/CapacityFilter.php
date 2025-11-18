<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class CapacityFilter implements FilterInterface
{
    public function apply(Builder $query, array $params): Builder
    {
        $minCapacity = isset($params['capacity_from']) && $params['capacity_from'] !== '' ? $params['capacity_from'] : null;
        $maxCapacity = isset($params['capacity_to']) && $params['capacity_to'] !== '' ? $params['capacity_to'] : null;

        if (method_exists($query->getModel(), 'scopeCapacityBetween')) {

            return $query->capacityBetween($minCapacity, $maxCapacity);
        }

        if ($minCapacity !== null) {
            $query->where('capacity', '>=', $minCapacity);
        }

        if ($maxCapacity !== null) {
            $query->where('capacity', '<=', $maxCapacity);
        }

        return $query;
    }
}
