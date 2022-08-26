<?php

namespace App\Trading\Bots\Pricing;

abstract class PriceCollection
{
    protected array $items;

    protected array $prices;

    protected array $times;

    protected int $count;

    public function __construct(
        protected string   $exchange,
        protected string   $ticker,
        protected Interval $interval,
        array              $prices,
        array              $times,
    )
    {
        $this->items = $prices;
        $this->prices = $this->createPrices();
        $this->times = $times;
        $this->count = count($this->items);
    }

    /**
     * @return float[]
     */
    abstract protected function createPrices(): array;

    public function items(): array
    {
        return $this->items;
    }

    /**
     * @return float[]
     */
    public function prices(): array
    {
        return $this->prices;
    }

    public function priceAt(int $index): float
    {
        return $this->prices()[$index];
    }

    /**
     * @return int[]
     */
    public function times(): array
    {
        return $this->times;
    }

    public function timeAt(int $index): int
    {
        return $index === $this->count
            ? $this->interval->getNextLatestTimeOf($this->latestTime())
            : $this->times()[$index];
    }

    public function count(): int
    {
        return $this->count;
    }

    public function latestTime(): int
    {
        return $this->timeAt($this->count - 1);
    }
}
