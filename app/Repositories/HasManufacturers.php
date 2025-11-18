<?php

namespace App\Repositories;

interface HasManufacturers
{
    /**
     * Return distinct manufacturers for this product type.
     *
     * @return array<string>
     */
    public function manufacturers(): array;
}
