<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class PowerFilter implements FilterInterface
{
    public function apply(Builder $query, array $params): Builder
    {
        $minPower = isset($params['power_from']) && $params['power_from'] !== '' ? $params['power_from'] : null;
        $maxPower = isset($params['power_to']) && $params['power_to'] !== '' ? $params['power_to'] : null;

        if (method_exists($query->getModel(), 'scopePowerBetween')) {

            return $query->powerBetween($minPower, $maxPower);
        }

        if ($minPower !== null) {
            $query->where('power_output', '>=', $minPower);
        }

        if ($maxPower !== null) {
            $query->where('power_output', '<=', $maxPower);
        }

        return $query;
    }
}
