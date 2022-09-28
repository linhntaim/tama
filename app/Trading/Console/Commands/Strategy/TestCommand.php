<?php

namespace App\Trading\Console\Commands\Strategy;

use App\Support\Console\Commands\Command;
use App\Trading\Bots\Exchanges\Binance\Binance;
use App\Trading\Bots\Oscillators\RsiOscillator;
use App\Trading\Bots\Tests\BotTest;
use App\Trading\Trader;

class TestCommand extends Command
{
    protected function handling(): int
    {
        out(
            (new BotTest(
                0.0,
                500.0,
                0.0,
                0.0,
                'oscillating_bot',
                [
                    'exchange' => Binance::NAME,
                    'ticker' => Binance::DEFAULT_TICKER,
                    'interval' => Trader::INTERVAL_1_HOUR,
                    'oscillator' => [
                        'name' => RsiOscillator::NAME,
                    ],
                ],
            ))->testYearsTillNow()
        );
        return $this->exitSuccess();
    }
}
