<?php

namespace App\Support\Trading\Strategies\Signals;

use App\Support\Trading\Strategies\Data\Data;

class RsiBullishSignal extends BullishSignal
{
    public const NAME = 'rsi';

    protected function calculateStrength(Data $data): float
    {
        // TODO: Implement calculateStrength() method.
    }
}
