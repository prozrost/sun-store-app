<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class PriceFilter implements FilterInterface
{
    public function apply(Builder $query, array $params): Builder
    {
        $minPrice = isset($params['price_from']) && $params['price_from'] !== '' ? $params['price_from'] : null;
        $maxPrice = isset($params['price_to']) && $params['price_to'] !== '' ? $params['price_to'] : null;

        if (method_exists($query->getModel(), 'scopePriceBetween')) {

            return $query->priceBetween($minPrice, $maxPrice);
        }

        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }

        return $query;
    }
}
