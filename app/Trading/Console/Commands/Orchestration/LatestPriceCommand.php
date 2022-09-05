<?php

namespace App\Trading\Console\Commands\Orchestration;

use App\Support\Console\Commands\Command;
use App\Trading\Bots\Actions\ReportAction;
use App\Trading\Bots\Exchanges\Binance\Binance;
use App\Trading\Bots\Exchanges\Binance\LatestPrice as BinanceLatestPrice;
use App\Trading\Bots\Exchanges\LatestPrice;
use App\Trading\Bots\Orchestrators\LatestPriceOrchestrator;
use InvalidArgumentException;

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
            $this->createLatestPrice(),
            [
                new ReportAction(),
            ]
        ))->proceed();
        return $this->exitSuccess();
    }

    public function createLatestPrice(): LatestPrice
    {
        return transform(
            $this->latestPriceClass(),
            fn($class) => new $class($this->ticker(), $this->interval(), $this->price())
        );
    }

    protected function latestPriceClass(): string
    {
        return match ($this->exchange()) {
            Binance::NAME => BinanceLatestPrice::class,
            default => throw new InvalidArgumentException(sprintf('Latest price for the exchange "%s" does not exists.', $this->exchange()))
        };
    }
}
