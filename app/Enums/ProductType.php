<?php

namespace App\Enums;

enum ProductType: string
{
    case BATTERIES = 'batteries';
    case CONNECTORS = 'connectors';
    case SOLAR_PANELS = 'solar_panels';

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
