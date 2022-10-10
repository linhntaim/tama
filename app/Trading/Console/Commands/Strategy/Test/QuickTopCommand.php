<?php

namespace App\Trading\Console\Commands\Strategy\Test;

use App\Support\Console\Commands\Command;
use App\Trading\Services\CoinMarketCap\Api\V1\CryptocurrencyApi;

class QuickTopCommand extends Command
{
    public $signature = '{--exchange=binance} {--interval=1h} {--start-time=4Y} {--end-time=} {--limit=100}';

    protected function exchange(): string
    {
        return strtolower($this->option('exchange'));
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

    protected function limit(): int
    {
        return (int)$this->option('limit');
    }

    protected function handling(): int
    {
        $tops = (new CryptocurrencyApi())->listingsLatest(1, $this->limit());
        foreach ($tops as $top) {
            $this->call('strategy:test:quick-symbol', [
                '--exchange' => $this->exchange(),
                '--symbol' => $top['symbol'],
                '--interval' => $this->interval(),
                '--start-time' => $this->startTime(),
                '--end-time' => $this->endTime(),
            ]);
        }
        return $this->exitSuccess();
    }
}