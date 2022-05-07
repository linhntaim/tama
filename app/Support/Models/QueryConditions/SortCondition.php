<?php

namespace App\Support\Models\QueryConditions;

use App\Support\Models\ModelProvider;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;

class SortCondition extends QueryCondition
{
    protected string|Closure|Expression $by;

    protected bool $ascending;

    public function __construct(string|Closure|Expression $by, bool $ascending = true)
    {
        $this
            ->setBy($by)
            ->setAscending($ascending);
    }

    public function setBy(string|Closure|Expression $by): static
    {
        $this->by = $by;
        return $this;
    }

    public function setAscending(bool $ascending): static
    {
        $this->ascending = $ascending;
        return $this;
    }

    public function __invoke(Builder $query): Builder
    {
        return $query->orderBy($this->by, $this->ascending ? 'asc' : 'desc');
    }
}
