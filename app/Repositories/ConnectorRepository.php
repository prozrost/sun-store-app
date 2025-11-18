<?php

namespace App\Repositories;

use App\Models\Connector;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use App\Http\DTOs\ProductQueryDTO;
use App\Filters\SearchFilter;
use App\Filters\ManufacturerFilter;
use App\Filters\PriceFilter;
use App\Filters\ConnectorTypeFilter;
use App\Filters\QueryFilters;

class ConnectorRepository implements ConnectorRepositoryInterface, HasManufacturers, HasConnectorTypes
{
    public function paginate(ProductQueryDTO $dto): LengthAwarePaginator
    {
        $query = Connector::query();

        $params = [
            'search' => $dto->search,
            'manufacturer' => $dto->manufacturer,
            'price_from' => $dto->priceFrom,
            'price_to' => $dto->priceTo,
            'connector_type' => $dto->connectorType,
        ];

        $filters = [
            SearchFilter::class,
            ManufacturerFilter::class,
            PriceFilter::class,
            ConnectorTypeFilter::class,
        ];

        $pipeline = new QueryFilters($params, $filters);
        $query = $pipeline->apply($query);

        return $query->orderBy('id')->paginate($dto->perPage);
    }

    public function manufacturers(): array
    {
        return Cache::remember('manufacturers:connectors', 3600, function () {
            return Connector::query()->select('manufacturer')->distinct()->orderBy('manufacturer')->pluck('manufacturer')->filter()->values()->all();
        });
    }


    public function connectorTypes(): ?array
    {
        return Cache::remember('connector_types', 3600, function () {
            return Connector::query()->select('connector_type')->distinct()->orderBy('connector_type')->pluck('connector_type')->filter()->values()->all();
        });
    }
}
