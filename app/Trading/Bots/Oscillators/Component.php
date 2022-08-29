<?php

namespace App\Trading\Bots\Oscillators;

use App\Trading\Bots\BotSlug;
use App\Trading\Bots\Pricing\PriceCollection;

abstract class Component
{
    use BotSlug;

    public const NAME = '';

    /**
     * @param array $options Must be a 1-dimension array
     */
    public function __construct(
        protected array $options = []
    )
    {
    }

    final public function getName(): string
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

    protected function optionsAsSlug(): string
    {
        return $this->slugConcat(...$this->options());
    }

    public function asSlug(): string
    {
        return $this->slugConcat(
            $this->getName(),
            $this->optionsAsSlug(),
        );
    }

    public function transmit(Packet $packet, bool|int $latest = true): Packet
    {
        return $this->transform(
            $this->analyze(
                $this->convert($packet),
                $latest
            )
        );
    }

    protected function getPrices(Packet $packet): PriceCollection
    {
        return $packet->get('inputs.prices');
    }

    abstract protected function convert(Packet $packet): Packet;

    abstract protected function analyze(Packet $packet, bool|int $latest = true): Packet;

    abstract protected function transform(Packet $packet): Packet;
}
