<?php

namespace App\Trading\Bots\Tests;

use App\Trading\Models\TradingStrategy;

class StrategyTest extends TradingTest
{
    public function __construct(
        TradingStrategy $strategy,
        string          $baseAmount = '0.0',
        string          $quoteAmount = '500.0',
        ?float          $buyRisk = null,
        ?float          $sellRisk = null,
    )
    {
        parent::__construct(
            $strategy->buyTradings,
            $strategy->sellTradings,
            $baseAmount,
            $quoteAmount,
            $buyRisk ?: $strategy->buy_risk,
            $sellRisk ?: $strategy->sell_risk,
        );
    }
}
