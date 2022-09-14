<?php

namespace App\Trading\Console\Commands\Orchestration;

use App\Support\Console\Commands\Command;
use App\Trading\Bots\Actions\ReportAction;
use App\Trading\Bots\Actions\TradeAction;
use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Bots\Orchestrators\LatestPriceOrchestrator;

class LatestPriceCommand extends Command
{
    public $signature = '{exchange} {ticker} {interval} {price}';

    protected function exchange(): string
    {
        return strtolower($this->argument('exchange'));
    }

    protected function ticker(): string
    {
        return strtoupper($this->argument('ticker'));
    }

    protected function interval(): string
    {
        return $this->argument('interval');
    }

    protected function price(): array
    {
        return json_decode_array(base64_decode($this->argument('price'))) ?: [];
    }

    protected function handling(): int
    {
        (new LatestPriceOrchestrator(
            Exchanger::exchange($this->exchange())->createLatestPrice(
                $this->ticker(),
                $this->interval(),
                $this->price()
            ),
            [
                new TradeAction(),
                new ReportAction(),
            ]
        ))->proceed();
        return $this->exitSuccess();
    }
}
