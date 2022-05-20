<?php

namespace App\Support\Models\QueryConditions;

use Illuminate\Database\Eloquent\Builder;

class WithCondition extends QueryCondition
{
    protected array $relations;

    public function __construct(array $relations)
    {
        $this->relations = $relations;
    }

    public function __invoke(Builder $query): Builder
    {
        return $query->with($this->relations);
    }
}
