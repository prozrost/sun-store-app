<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class ConnectorTypeFilter implements FilterInterface
{
    public function apply(Builder $query, array $params): Builder
    {
        $connectorType = $params['connector_type'] ?? null;

        if (! $connectorType) {

            return $query;
        }

        if (method_exists($query->getModel(), 'scopeConnectorType')) {

            return $query->connectorType($connectorType);
        }

        return $query->where('connector_type', $connectorType);
    }
}
