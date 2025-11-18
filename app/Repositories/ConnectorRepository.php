<?php

namespace App\Repositories;

use App\Models\Connector;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ConnectorRepository implements ConnectorRepositoryInterface
{
    public function paginate(
        int $perPage = 10,
        ?string $search = null,
        ?string $manufacturer = null,
        ?float $priceFrom = null,
        ?float $priceTo = null,
        ?float $capacityFrom = null,
        ?float $capacityTo = null,
        ?float $powerFrom = null,
        ?float $powerTo = null,
        ?string $connectorType = null
    ): LengthAwarePaginator {
        $query = Connector::query();

        $params = [
            'search' => $search,
            'manufacturer' => $manufacturer,
            'price_from' => $priceFrom,
            'price_to' => $priceTo,
            'connector_type' => $connectorType,
        ];

        $filters = [
            \App\Filters\SearchFilter::class,
            \App\Filters\ManufacturerFilter::class,
            \App\Filters\PriceFilter::class,
            \App\Filters\ConnectorTypeFilter::class,
        ];

        $pipeline = new \App\Filters\QueryFilters($params, $filters);
        $query = $pipeline->apply($query);

        return $query->orderBy('id')->paginate($perPage);
    }

    public function manufacturers(): array
    {
        return Cache::remember('manufacturers:connectors', 3600, function () {
            return Connector::query()->select('manufacturer')->distinct()->orderBy('manufacturer')->pluck('manufacturer')->filter()->values()->all();
        });
    }

    public function capacityRange(): ?array
    {
        return null;
    }

    public function powerRange(): ?array
    {
        return null;
    }

    public function connectorTypes(): ?array
    {
        return Cache::remember('connector_types', 3600, function () {
            return Connector::query()->select('connector_type')->distinct()->orderBy('connector_type')->pluck('connector_type')->filter()->values()->all();
        });
    }
}
