<?php

namespace App\Http\DTOs;

use App\Enums\ProductType;

class ProductQueryDTO
{
    public ProductType $type;
    public ?string $search;
    public ?string $manufacturer;
    public int $perPage;
    public ?float $priceFrom;
    public ?float $priceTo;
    public ?float $capacityFrom;
    public ?float $capacityTo;
    public ?float $powerFrom;
    public ?float $powerTo;
    public ?string $connectorType;

    public function __construct(array $validated)
    {
        $this->type = ProductType::tryFrom($validated['type'] ?? ProductType::BATTERIES->value) ?? ProductType::BATTERIES;
        $this->search = $validated['q'] ?? null;
        $this->manufacturer = $validated['manufacturer'] ?? null;
        $this->perPage = 10;
        $this->priceFrom = isset($validated['price_from']) && $validated['price_from'] !== '' ? (float) $validated['price_from'] : null;
        $this->priceTo = isset($validated['price_to']) && $validated['price_to'] !== '' ? (float) $validated['price_to'] : null;
        $this->capacityFrom = isset($validated['capacity_from']) && $validated['capacity_from'] !== '' ? (float) $validated['capacity_from'] : null;
        $this->capacityTo = isset($validated['capacity_to']) && $validated['capacity_to'] !== '' ? (float) $validated['capacity_to'] : null;
        $this->powerFrom = isset($validated['power_from']) && $validated['power_from'] !== '' ? (float) $validated['power_from'] : null;
        $this->powerTo = isset($validated['power_to']) && $validated['power_to'] !== '' ? (float) $validated['power_to'] : null;
        $this->connectorType = $validated['connector_type'] ?? null;
    }
}
