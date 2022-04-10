<?php

namespace App\Support\ModelProviders;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

class WhereCondition extends QueryCondition
{
    protected string|array|Closure|Expression $name;

    protected string $operator;

    protected mixed $value;

    public function __construct(string|array|Closure|Expression $name, mixed $value, string $operator = '=')
    {
        $this
            ->setName($name)
            ->setValue($value)
            ->setOperator($operator);
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

    public function __invoke(Builder $query): Builder
    {
        return $query->where($this->name, $this->operator, $this->value);
    }
}