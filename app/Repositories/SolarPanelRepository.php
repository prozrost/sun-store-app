<?php

namespace App\Repositories;

use App\Models\SolarPanel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use App\Http\DTOs\ProductQueryDTO;
use App\Filters\SearchFilter;
use App\Filters\ManufacturerFilter;
use App\Filters\PriceFilter;
use App\Filters\PowerFilter;
use App\Filters\QueryFilters;

class SolarPanelRepository implements SolarPanelRepositoryInterface, HasManufacturers, HasPowerRange
{
    public function paginate(ProductQueryDTO $dto): LengthAwarePaginator
    {
        $query = SolarPanel::query();

        $params = [
            'search' => $dto->search,
            'manufacturer' => $dto->manufacturer,
            'price_from' => $dto->priceFrom,
            'price_to' => $dto->priceTo,
            'power_from' => $dto->powerFrom,
            'power_to' => $dto->powerTo,
        ];

        $filters = [
            SearchFilter::class,
            ManufacturerFilter::class,
            PriceFilter::class,
            PowerFilter::class,
        ];

        $pipeline = new QueryFilters($params, $filters);
        $query = $pipeline->apply($query);

        return $query->orderBy('id')->paginate($dto->perPage);
    }

    public function manufacturers(): array
    {
        return Cache::remember('manufacturers:solar_panels', 3600, function () {
            return SolarPanel::query()->select('manufacturer')->distinct()->orderBy('manufacturer')->pluck('manufacturer')->filter()->values()->all();
        });
    }


    public function powerRange(): ?array
    {
        $min = SolarPanel::query()->min('power_output');
        $max = SolarPanel::query()->max('power_output');
        if ($min === null && $max === null) {
            return null;
        }

        return ['min' => $min, 'max' => $max];
    }

}
