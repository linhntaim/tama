<?php

namespace App\Trading\Bots\Exchanges;

use App\Support\ArrayReader;
use App\Trading\Trader;

class UiIntervals extends ArrayReader
{
    public function __construct(array $intervals, string $default = Trader::INTERVAL_1_DAY)
    {
        parent::__construct([
            'intervals' => $intervals,
            'default' => $default,
        ]);
    }
}