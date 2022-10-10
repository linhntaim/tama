<?php

namespace App\Trading\Console\Commands\Strategy\Test;

use App\Support\Console\Commands\Command;
use App\Trading\Bots\Exchanges\Exchanger;
use Throwable;

class QuickSymbolCommand extends Command
{
    public $signature = '{--exchange=binance} {--symbol=BTC} {--interval=1h} {--start-time=4Y} {--end-time=}';

    protected function exchange(): string
    {
        return strtolower($this->option('exchange'));
    }

    protected function symbol(): string
    {
        return strtoupper($this->option('symbol'));
    }

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

    protected function handling(): int
    {
        if (!in_array($symbol = $this->symbol(), array_merge(Exchanger::STABLECOIN_SYMBOLS, Exchanger::GOLDCOIN_SYMBOLS), true)) {
            $connector = Exchanger::connector($exchange = $this->exchange());
            try {
                $connector->symbolPrice($symbol, $usdSymbol);
                $this->callSilent('strategy:test:quick', [
                    '--exchange' => $exchange,
                    '--ticker' => $connector->createTicker($symbol, $usdSymbol),
                    '--interval' => $this->interval(),
                    '--start-time' => $this->startTime(),
                    '--end-time' => $this->endTime(),
                ]);
            }
            catch (Throwable) {
                $this->error(sprintf('Cannot trade symbol "%s" on the exchange "%s".', $symbol, $exchange));
                return $this->exitFailure();
            }
        }
        return $this->exitSuccess();
    }
}