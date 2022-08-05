<?php

namespace App\Trading\Bots\Oscillators;

use App\Trading\Prices\Prices;

abstract class Component
{
    public const NAME = '';

    /**
     * @param array $options Must be a 1-dimension array
     */
    public function __construct(
        protected array $options = []
    )
    {
    }

    public final function getName(): string
    {
        return static::NAME;
    }

    public function options(): array
    {
        return $this->options;
    }

    public function asOptions(): array
    {
        return [
            'name' => $this->getName(),
            'options' => $this->options(),
        ];
    }

    public function asSlug(): string
    {
        return implode('-', [
            $this->getName(),
            ...$this->options(),
        ]);
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
