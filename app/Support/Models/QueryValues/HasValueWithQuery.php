<?php

namespace App\Support\Models\QueryValues;

use Closure;

class HasValueWithQuery extends HasValue
{
    public function __construct(Closure $callback, string $operator = '>=', int $count = 1)
    {
        parent::__construct($operator, $count, $callback);
    }
}