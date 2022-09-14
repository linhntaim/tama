<?php

namespace App\Trading\Bots\Tests;

use App\Trading\Models\Trading;

class TradingTest extends StrategyTest
{
    public function __construct(
        Trading $buyTrading,
        Trading $sellTrading,
        string  $baseAmount = '0.0',
        string  $quoteAmount = '500.0',
        float   $buyRisk = 0.0,
        float   $sellRisk = 0.0,
    )
    {
        parent::__construct(
            $baseAmount,
            $quoteAmount,
            $buyRisk,
            $sellRisk,
            $buyTrading->bot,
            $buyTrading->botOptions,
            $sellTrading->bot,
            $sellTrading->botOptions,
        );
    }
}