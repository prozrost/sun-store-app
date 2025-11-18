<?php

namespace App\Strategies;

use App\Repositories\ConnectorRepositoryInterface;
use App\Repositories\ProductRepositoryInterface;

class ConnectorStrategy implements ProductStrategyInterface
{
    protected ConnectorRepositoryInterface $repository;

    public function __construct(ConnectorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function repository(): ProductRepositoryInterface
    {
        return $this->repository;
    }
}
