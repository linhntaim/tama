<?php

namespace App\Support\Models\QueryValues;

use Stringable;

class LikeValue implements Stringable
{
    public function __construct(protected string $string)
    {
    }

    public function __toString(): string
    {
        return sprintf('%%%s%%', $this->string);
    }
}
