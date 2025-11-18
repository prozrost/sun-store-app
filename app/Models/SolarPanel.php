<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SolarPanel extends Model
{
    protected $table = 'solar_panels';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'name', 'manufacturer', 'price', 'power_output', 'description'];

    public $timestamps = false;

    /**
     * Search scope: name prefix, manufacturer prefix, or full-text on description.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {

            return $query;
        }

        return $query->where(function ($builder) use ($search) {
            $builder->where('name', 'LIKE', "{$search}%")
                ->orWhere('manufacturer', 'LIKE', "{$search}%")
                ->orWhereRaw("to_tsvector('simple', coalesce(name,'') || ' ' || coalesce(manufacturer,'') || ' ' || coalesce(description,'')) @@ websearch_to_tsquery('simple', ?)", [$search]);
        });
    }

    public function scopePriceBetween(Builder $query, $from = null, $to = null): Builder
    {
        if ($from !== null) {
            $query->where('price', '>=', $from);
        }
        if ($to !== null) {
            $query->where('price', '<=', $to);
        }

        return $query;
    }

    public function scopePowerBetween(Builder $query, $from = null, $to = null): Builder
    {
        if ($from !== null) {
            $query->where('power_output', '>=', $from);
        }
        if ($to !== null) {
            $query->where('power_output', '<=', $to);
        }

        return $query;
    }
}
