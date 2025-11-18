<?php

namespace App\Repositories;

use App\Models\SolarPanel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class SolarPanelRepository implements SolarPanelRepositoryInterface
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
        $query = SolarPanel::query();

        $params = [
            'search' => $search,
            'manufacturer' => $manufacturer,
            'price_from' => $priceFrom,
            'price_to' => $priceTo,
            'power_from' => $powerFrom,
            'power_to' => $powerTo,
        ];

        $filters = [
            \App\Filters\SearchFilter::class,
            \App\Filters\ManufacturerFilter::class,
            \App\Filters\PriceFilter::class,
            \App\Filters\PowerFilter::class,
        ];

        $pipeline = new \App\Filters\QueryFilters($params, $filters);
        $query = $pipeline->apply($query);

        return $query->orderBy('id')->paginate($perPage);
    }

    public function manufacturers(): array
    {
        return Cache::remember('manufacturers:solar_panels', 3600, function () {
            return SolarPanel::query()->select('manufacturer')->distinct()->orderBy('manufacturer')->pluck('manufacturer')->filter()->values()->all();
        });
    }

    public function capacityRange(): ?array
    {
        return null;
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

    public function connectorTypes(): ?array
    {
        return null;
    }
}
