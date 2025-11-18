<?php

namespace App\Strategies;

use App\Repositories\BatteryRepositoryInterface;
use App\Repositories\ProductRepositoryInterface;

class BatteryStrategy implements ProductStrategyInterface
{
    protected BatteryRepositoryInterface $repository;

    public function __construct(BatteryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function repository(): ProductRepositoryInterface
    {
        return $this->repository;
    }
}
