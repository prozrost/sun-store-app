<?php

namespace App\Repositories;

use App\Models\Battery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class BatteryRepository implements BatteryRepositoryInterface
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
        $query = Battery::query();

        $params = [
            'search' => $search,
            'manufacturer' => $manufacturer,
            'price_from' => $priceFrom,
            'price_to' => $priceTo,
            'capacity_from' => $capacityFrom,
            'capacity_to' => $capacityTo,
        ];

        $filters = [
            \App\Filters\SearchFilter::class,
            \App\Filters\ManufacturerFilter::class,
            \App\Filters\PriceFilter::class,
            \App\Filters\CapacityFilter::class,
        ];

        $pipeline = new \App\Filters\QueryFilters($params, $filters);
        $query = $pipeline->apply($query);

        return $query->orderBy('id')->paginate($perPage);
    }

    public function manufacturers(): array
    {
        return Cache::remember('manufacturers:batteries', 3600, function () {
            return Battery::query()->select('manufacturer')->distinct()->orderBy('manufacturer')->pluck('manufacturer')->filter()->values()->all();
        });
    }

    public function capacityRange(): ?array
    {
        $min = Battery::query()->min('capacity');
        $max = Battery::query()->max('capacity');
        if ($min === null && $max === null) {
            return null;
        }

        return ['min' => $min, 'max' => $max];
    }

    public function powerRange(): ?array
    {
        return null;
    }

    public function connectorTypes(): ?array
    {
        return null;
    }
}
