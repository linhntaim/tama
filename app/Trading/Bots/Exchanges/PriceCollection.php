<?php

namespace App\Trading\Bots\Exchanges;

use InvalidArgumentException;

abstract class PriceCollection
{
    protected array $items;

    /**
     * @var float[]
     */
    protected array $prices;

    /**
     * @var int[]
     */
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

    abstract protected function create(
        string   $exchange,
        string   $ticker,
        Interval $interval,
        array    $prices,
        array    $times,
    ): static;

    public function getExchange(): string
    {
        return $this->exchange;
    }

    /**
     * @return string
     */
    public function getTicker(): string
    {
        return $this->ticker;
    }

    /**
     * @return Interval
     */
    public function getInterval(): Interval
    {
        return $this->interval;
    }

    public function push(PriceCollection $priceCollection): static
    {
        if (!($priceCollection::class === static::class
            && $this->exchange === $priceCollection->getExchange()
            && $this->ticker === $priceCollection->getTicker()
            && $this->interval->eq($priceCollection->getInterval()))) {
            throw new InvalidArgumentException('Price collection does not match.');
        }

        array_push($this->items, ...$priceCollection->items());
        array_push($this->prices, ...$priceCollection->prices());
        array_push($this->times, ...$priceCollection->times());
        $this->count += $priceCollection->count();
        return $this;
    }

    public function slice(int $offset, ?int $length = null): static
    {
        return $this->create(
            $this->exchange,
            $this->ticker,
            $this->interval,
            array_slice($this->items, $offset, $length),
            array_slice($this->times, $offset, $length),
        );
    }

    /**
     * @return float[]
     */
    abstract protected function createPrices(): array;

    public function items(): array
    {
        return $this->items;
    }

    public function itemAt(int $index = 0): mixed
    {
        return $this->items()[$index];
    }

    /**
     * @return float[]
     */
    public function prices(): array
    {
        return $this->prices;
    }

    public function priceAt(int $index = 0): float
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

    public function timeAt(int $index = 0): int
    {
        return $index === $this->count
            ? $this->interval->getNextOpenTimeOfExact($this->latestTime())
            : $this->times()[$index];
    }

    public function count(): int
    {
        return $this->count;
    }

    public function latestItem(): mixed
    {
        return $this->itemAt($this->count - 1);
    }

    public function latestPrice(): float
    {
        return $this->priceAt($this->count - 1);
    }

    public function latestTime(): int
    {
        return $this->timeAt($this->count - 1);
    }
}
