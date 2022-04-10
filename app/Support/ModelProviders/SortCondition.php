<?php

namespace App\Support\ModelProviders;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

class SortCondition extends QueryCondition
{
    protected string|Closure|Expression $by;

    protected string $direction;

    public function __construct(string|Closure|Expression $by, string $direction = ModelProvider::SORT_ASC)
    {
        $this
            ->setBy($by)
            ->setDirection($direction);
    }

    public function setBy(string|Closure|Expression $by): static
    {
        $this->by = $by;
        return $this;
    }

    public function setDirection(string $direction): static
    {
        $this->direction = $direction;
        return $this;
    }

    public function __invoke(Builder $query): Builder
    {
        return $query->orderBy($this->by, $this->direction);
    }
}