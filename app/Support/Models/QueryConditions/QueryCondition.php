<?php

namespace App\Support\Models\QueryConditions;

use Illuminate\Database\Eloquent\Builder;

abstract class QueryCondition
{
    public function __invoke(Builder $query): Builder
    {
        return $query;
    }
}