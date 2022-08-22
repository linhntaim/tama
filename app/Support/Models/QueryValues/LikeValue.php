<?php

namespace App\Support\Models\QueryValues;

use Stringable;

class LikeValue implements Stringable
{
    public static function create(string $string): static
    {
        return new static($string);
    }

    public function __construct(protected string $string)
    {
    }

    public function __toString(): string
    {
        return sprintf('%%s%', $this->string);
    }
}
