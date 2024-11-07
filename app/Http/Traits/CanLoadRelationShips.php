<?php

namespace App\Http\TRaits;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

trait CanLoadRelationships
{

    public function loadRelationShips(Model|EloquentBuilder|QueryBuilder  $for, ?array $relations = null): Model|EloquentBuilder|QueryBuilder
    {

        $relations = $relations ?? $this->relations ?? [];

        foreach ($relations as $relation) {
            $for->when(
                $this->shouldIncludeRelation($relation),
                fn($q) => $for instanceof Model ? $for->load($relation) :  $q->with($relation)
            );
        }

        return $for;
    }

    protected function shouldIncludeRelation(string $relation): bool
    {

        $include = request()->query('include');

        if (!$include) {
            return false;
        }

        $relations = explode(',', $include);

        return in_array($relation, $relations);
    }
}
