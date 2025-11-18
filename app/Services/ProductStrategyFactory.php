<?php

namespace App\Services;

use App\Enums\ProductType;
use App\Strategies\BatteryStrategy;
use App\Strategies\ConnectorStrategy;
use App\Strategies\ProductStrategyInterface;
use App\Strategies\SolarPanelStrategy;

class ProductStrategyFactory
{
    public static function make(ProductType $type): ProductStrategyInterface
    {
        return match ($type) {
            ProductType::BATTERIES => app()->make(BatteryStrategy::class),
            ProductType::CONNECTORS => app()->make(ConnectorStrategy::class),
            ProductType::SOLAR_PANELS => app()->make(SolarPanelStrategy::class),
        };
    }
}
