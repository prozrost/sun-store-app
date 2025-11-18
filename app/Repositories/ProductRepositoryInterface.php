<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Http\DTOs\ProductQueryDTO;

interface ProductRepositoryInterface
{
    /**
     * Paginate products using query DTO.
     */
    public function paginate(ProductQueryDTO $dto): LengthAwarePaginator;
}
