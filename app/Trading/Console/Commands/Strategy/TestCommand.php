<?php

namespace App\Trading\Console\Commands\Strategy;

use App\Support\Console\Commands\Command;
use App\Trading\Bots\Exchanges\Binance\Binance;
use App\Trading\Bots\Oscillators\RsiOscillator;
use App\Trading\Bots\Tests\StrategyTest;
use App\Trading\Trader;

class TestCommand extends Command
{
    protected function handling(): int
    {
        out(
            (new StrategyTest(
                0.0,
                500.0,
                0.0,
                0.0,
                'oscillating_bot',
                [
                    'exchange' => Binance::NAME,
                    'ticker' => Binance::DEFAULT_TICKER,
                    'interval' => Trader::INTERVAL_1_DAY,
                    'oscillator' => [
                        'name' => RsiOscillator::NAME,
                    ],
                ],
            ))->testYearsTillNow(6)
        );
        return $this->exitSuccess();
    }
}
