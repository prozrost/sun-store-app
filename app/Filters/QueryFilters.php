<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Compose and apply an ordered list of filters to a query builder.
 */
class QueryFilters
{
    /** @var array<int, class-string<FilterInterface>> */
    protected array $filters = [];

    protected array $params = [];

    /**
     * @param  array  $params  Request or repository params used by filters
     * @param  array  $filters  Ordered list of Filter classes to apply
     */
    public function __construct(array $params, array $filters)
    {
        $this->params = $params;
        $this->filters = $filters;
    }

    public function apply(Builder $query): Builder
    {
        foreach ($this->filters as $filterClass) {
            if (! class_exists($filterClass)) {
                continue;
            }
            /** @var FilterInterface $filter */
            $filterInstance = new $filterClass;
            $query = $filterInstance->apply($query, $this->params);
        }

        return $query;
    }
}
