<?php

namespace App\Support\Models\QueryValues;

use Closure;

class DoesntHaveValue extends HasValue
{
    public function __construct(?Closure $callback = null)
    {
        parent::__construct('<', 1, $callback);
    }
}
