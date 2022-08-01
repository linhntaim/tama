<?php

namespace App\Support\TradingSystem\Bots\Oscillators;

use App\Support\TradingSystem\Prices\Prices;

abstract class Component
{
    public function __construct(
        protected array $options = []
    )
    {
    }

    public function transmit(Packet $packet): Packet
    {
        return $this->transform(
            $this->analyze(
                $this->convert($packet)
            )
        );
    }

    protected function getPrices(Packet $packet): Prices
    {
        return $packet->get('inputs.prices');
    }

    protected abstract function convert(Packet $packet): Packet;

    protected abstract function analyze(Packet $packet): Packet;

    protected abstract function transform(Packet $packet): Packet;
}
