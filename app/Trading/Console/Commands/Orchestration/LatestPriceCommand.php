<?php

namespace App\Trading\Console\Commands\Orchestration;

use App\Support\Console\Commands\Command;
use App\Trading\Bots\Actions\ReportAction;
use App\Trading\Bots\Orchestrators\LatestPriceOrchestrator;
use App\Trading\Bots\Pricing\LatestPriceFactory;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

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
        return json_decode_array(base64_decode($this->argument('price')));
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    protected function handling(): int
    {
        (new LatestPriceOrchestrator(
            LatestPriceFactory::create(
                $this->exchange(),
                $this->ticker(),
                $this->interval(),
                $this->price()
            ),
            [new ReportAction()]
        ))->proceed();
        return $this->exitSuccess();
    }
}
