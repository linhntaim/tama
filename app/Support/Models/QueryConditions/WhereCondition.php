<?php

namespace App\Support\Models\QueryConditions;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

class WhereCondition extends QueryCondition
{
    protected string|array|Closure|Expression $name;

    protected string $operator;

    protected mixed $value;

    protected string $boolean;

    public function __construct(string|array|Closure|Expression $name, mixed $value, string $operator = '=', $boolean = 'and')
    {
        $this
            ->setName($name)
            ->setValue($value)
            ->setOperator($operator)
            ->setBoolean($boolean);
    }

    public function setName(string|array|Closure|Expression $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function setOperator(string $operator): static
    {
        $this->operator = $operator;
        return $this;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function setBoolean(string $boolean): static
    {
        $this->boolean = $boolean;
        return $this;
    }

    public function __invoke(Builder $query): Builder
    {
        return $query->where($this->name, $this->operator, $this->value, $this->boolean);
    }
}