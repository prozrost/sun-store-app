<?php

namespace App\Strategies;

use App\Repositories\ProductRepositoryInterface;
use App\Repositories\SolarPanelRepositoryInterface;

class SolarPanelStrategy implements ProductStrategyInterface
{
    protected SolarPanelRepositoryInterface $repository;

    public function __construct(SolarPanelRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function repository(): ProductRepositoryInterface
    {
        return $this->repository;
    }
}
