<?php

namespace App\Trading\Bots\Tests;

use Illuminate\Support\Collection;

class ReportTest
{
    public function __construct(
        protected Collection $swaps
    )
    {
    }

    public function report(): string
    {
    }
}
