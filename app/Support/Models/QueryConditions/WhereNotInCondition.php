<?php

namespace App\Support\Models\QueryConditions;

class WhereNotInCondition extends WhereInCondition
{
    public function __construct(string $column, mixed $values, string $boolean = 'and')
    {
        parent::__construct($column, $values, $boolean, true);
    }
}
