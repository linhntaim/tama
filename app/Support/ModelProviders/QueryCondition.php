<?php

namespace App\Support\ModelProviders;

use Illuminate\Database\Eloquent\Builder;

abstract class QueryCondition
{
    public function __invoke(Builder $query): Builder
    {
        return $query;
    }
}