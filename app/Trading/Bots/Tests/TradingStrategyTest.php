<?php

namespace App\Trading\Bots\Tests;

use App\Trading\Models\TradingStrategy;

class TradingStrategyTest extends StrategyTest
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
            $baseAmount,
            $quoteAmount,
            $buyRisk ?: $strategy->buy_risk,
            $sellRisk ?: $strategy->sell_risk,
            $strategy->buyTrading->bot,
            $strategy->buyTrading->botOptions,
            $strategy->sellTrading->bot,
            $strategy->sellTrading->botOptions,
        );
    }
}
