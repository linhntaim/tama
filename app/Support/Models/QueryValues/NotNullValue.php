<?php

namespace App\Support\Models\QueryValues;

class NotNullValue
{
    public static function create(): static
    {
        return new static();
    }
}
