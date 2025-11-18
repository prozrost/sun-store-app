<?php

namespace App\Repositories;

use App\Models\Battery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use App\Http\DTOs\ProductQueryDTO;
use App\Filters\SearchFilter;
use App\Filters\ManufacturerFilter;
use App\Filters\PriceFilter;
use App\Filters\CapacityFilter;
use App\Filters\QueryFilters;

class BatteryRepository implements BatteryRepositoryInterface, HasManufacturers, HasCapacityRange
{
    public function paginate(ProductQueryDTO $dto): LengthAwarePaginator
    {
        $query = Battery::query();

        $params = [
            'search' => $dto->search,
            'manufacturer' => $dto->manufacturer,
            'price_from' => $dto->priceFrom,
            'price_to' => $dto->priceTo,
            'capacity_from' => $dto->capacityFrom,
            'capacity_to' => $dto->capacityTo,
        ];

        $filters = [
            SearchFilter::class,
            ManufacturerFilter::class,
            PriceFilter::class,
            CapacityFilter::class,
        ];

        $pipeline = new QueryFilters($params, $filters);
        $query = $pipeline->apply($query);

        return $query->orderBy('id')->paginate($dto->perPage);
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

}
