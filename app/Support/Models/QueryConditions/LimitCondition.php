<?php

namespace App\Support\Models\QueryConditions;

use Illuminate\Database\Eloquent\Builder;

class LimitCondition extends QueryCondition
{
    protected int $limit;

    protected int $skip;

    public function __construct(int $limit, int $skip = 0)
    {
        $this
            ->setLimit($limit)
            ->setSkip($skip);
    }

    public function setLimit(int $limit): static
    {
        $this->limit = $limit > 0 ? $limit : 1;
        return $this;
    }

    public function setSkip(int $skip): static
    {
        $this->skip = max($skip, 0);
        return $this;
    }

    public function __invoke(Builder $query): Builder
    {
        return $query->skip($this->skip)->limit($this->limit);
    }
}
