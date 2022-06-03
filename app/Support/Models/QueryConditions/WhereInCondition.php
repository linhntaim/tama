<?php

namespace App\Support\Models\QueryConditions;

use Illuminate\Database\Eloquent\Builder;

class WhereInCondition extends QueryCondition
{
    protected string $column;

    protected mixed $values;

    protected string $boolean;

    protected bool $not;

    public function __construct(string $column, mixed $values, string $boolean = 'and', bool $not = false)
    {
        $this->column = $column;
        $this->values = $values;
        $this->boolean = $boolean;
        $this->not = $not;
    }

    public function __invoke(Builder $query): Builder
    {
        return $query->whereIn($this->column, $this->values, $this->boolean, $this->not);
    }
}
