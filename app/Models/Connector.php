<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Connector extends Model
{
    protected $table = 'connectors';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'name', 'manufacturer', 'price', 'connector_type', 'description'];

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

    public function scopeConnectorType(Builder $query, ?string $type): Builder
    {
        if ($type) {
            $query->where('connector_type', $type);
        }

        return $query;
    }
}
