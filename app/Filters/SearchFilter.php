<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class SearchFilter implements FilterInterface
{
    public function apply(Builder $query, array $params): Builder
    {
        $search = $params['search'] ?? null;

        if (! $search) {

            return $query;
        }
        // delegate to model scope `search` if present
        if (method_exists($query->getModel(), 'scopeSearch')) {

            return $query->search($search);
        }

        // fallback: escape LIKE wildcards for prefix matching and use a sanitized FTS string
        $escaped = $this->escapeLike((string) $search).'%';
        $tsQuery = $this->sanitizeForTsQuery((string) $search);

        return $query->where(function ($builder) use ($escaped, $tsQuery) {
            $builder->where('name', 'LIKE', $escaped)
                ->orWhere('manufacturer', 'LIKE', $escaped)
                ->orWhereRaw("to_tsvector('simple', coalesce(name,'') || ' ' || coalesce(manufacturer,'') || ' ' || coalesce(description,'')) @@ websearch_to_tsquery('simple', ?)", [$tsQuery]);
        });
    }

    private function escapeLike(string $value): string
    {
        // Escape backslash first, then % and _ for SQL LIKE
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('%', '\\%', $value);
        $value = str_replace('_', '\\_', $value);

        return $value;
    }

    private function sanitizeForTsQuery(string $value): string
    {
        // Remove characters that commonly break tsquery parsing and normalize whitespace.
        // Keep letters, numbers, spaces, +, -, and * for prefix operators.
        $clean = preg_replace('/[^\p{L}\p{N}\s\-\+\*]/u', ' ', $value);
        $clean = preg_replace('/\s+/u', ' ', $clean);

        return trim($clean);
    }
}
