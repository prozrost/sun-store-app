<?php

namespace App\Strategies;

use App\Repositories\ProductRepositoryInterface;

interface ProductStrategyInterface
{
    public function repository(): ProductRepositoryInterface;
}
