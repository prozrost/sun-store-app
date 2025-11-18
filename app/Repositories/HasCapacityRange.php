<?php

namespace App\Repositories;

interface HasCapacityRange
{
    public function capacityRange(): ?array;
}
