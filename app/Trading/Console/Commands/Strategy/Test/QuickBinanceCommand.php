<?php

namespace App\Trading\Console\Commands\Strategy\Test;

use App\Support\Console\Commands\Command;
use App\Trading\Bots\Exchanges\Binance\Binance;
use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Bots\Exchanges\Ticker;
use Illuminate\Support\Collection;

class QuickBinanceCommand extends Command
{
    public $signature = '{--interval=1h} {--start-time=4Y} {--end-time=} {--limit=100}';

    protected function interval(): string
    {
        return $this->option('interval');
    }

    protected function startTime(): ?string
    {
        return $this->option('start-time');
    }

    protected function endTime(): ?string
    {
        return $this->option('end-time');
    }

    protected function limit(): int
    {
        return (int)$this->option('limit');
    }

    protected function handling(): int
    {
        $tickers = Exchanger::connector(Binance::NAME)->availableTickers(
            ['USDT', 'BUSD'],
            null,
            null,
            array_merge(Exchanger::STABLECOIN_SYMBOLS, Exchanger::GOLDCOIN_SYMBOLS)
        );
        $this->info('TICKERS:');
        out($tickers->map(fn(Ticker $ticker) => $ticker->getSymbol())->all());
        foreach ($tickers as $i => $ticker) {
            $this->warn(sprintf('#%d %s...', $i, $ticker));
            $this->callSilent('strategy:test:quick', [
                '--exchange' => Binance::NAME,
                '--ticker' => $ticker->getSymbol(),
                '--interval' => $this->interval(),
                '--start-time' => $this->startTime(),
                '--end-time' => $this->endTime(),
            ]);
            $this->info('Done.');
        }
        return $this->exitSuccess();
    }
}