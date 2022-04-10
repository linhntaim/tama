<?php

namespace App\Support\Models\QueryConditions;

use Illuminate\Database\Eloquent\Builder;

class SelectCondition extends QueryCondition
{
    protected array $columns;

    public function __construct(array $columns = ['*'])
    {
        $this->columns = $columns;
    }

    public function __invoke(Builder $query): Builder
    {
        return $query->select($this->columns);
    }
}