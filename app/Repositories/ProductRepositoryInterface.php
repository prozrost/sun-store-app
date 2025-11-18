<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * Paginate products with optional search query.
     */
    public function paginate(int $perPage = 10, ?string $search = null, ?string $manufacturer = null, ?float $priceFrom = null, ?float $priceTo = null, ?float $capacityFrom = null, ?float $capacityTo = null, ?float $powerFrom = null, ?float $powerTo = null, ?string $connectorType = null): LengthAwarePaginator;

    /**
     * Return distinct manufacturers for this product type.
     *
     * @return array<string>
     */
    public function manufacturers(): array;

    public function capacityRange(): ?array;

    public function powerRange(): ?array;

    public function connectorTypes(): ?array;
}
