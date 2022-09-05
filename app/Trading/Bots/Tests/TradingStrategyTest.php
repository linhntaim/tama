<?php

namespace App\Trading\Bots\Tests;

use App\Trading\Models\TradingStrategy;

class TradingStrategyTest extends StrategyTest
{
    public function __construct(
        TradingStrategy $strategy,
        float           $baseAmount = 500.0,
        float           $quoteAmount = 0.0,
    )
    {
        parent::__construct(
            $baseAmount,
            $quoteAmount,
            $strategy->buy_risk,
            $strategy->sell_risk,
            $strategy->buyTrading->bot,
            $strategy->buyTrading->botOptions,
            $strategy->sellTrading->bot,
            $strategy->sellTrading->botOptions,
        );
    }
}
