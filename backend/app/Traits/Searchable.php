<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    public function scopeSearch(Builder $query, string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        $searchableFields = $this->getSearchableFields();
        
        return $query->where(function (Builder $query) use ($term, $searchableFields) {
            foreach ($searchableFields as $field) {
                if (str_contains($field, '.')) {
                    // Handle relationship fields
                    [$relation, $relationField] = explode('.', $field, 2);
                    $query->orWhereHas($relation, function (Builder $query) use ($relationField, $term) {
                        $query->where($relationField, 'LIKE', "%{$term}%");
                    });
                } else {
                    // Handle direct fields
                    $query->orWhere($field, 'LIKE', "%{$term}%");
                }
            }
        });
    }

    protected function getSearchableFields(): array
    {
        return $this->searchable ?? ['name'];
    }
}