<?php

namespace App\Support\Models\QueryValues;

use Closure;

class HasValue
{
    public function __construct(
        protected string   $operator = '>=',
        protected int      $count = 1,
        protected ?Closure $callback = null
    )
    {
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getCallback(): ?Closure
    {
        return $this->callback;
    }
}
