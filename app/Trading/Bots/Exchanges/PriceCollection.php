<?php

namespace App\Trading\Bots\Exchanges;

use InvalidArgumentException;

class PriceCollection
{
    /**
     * @var string[] Float-valued strings.
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
        protected array    $items,
    )
    {
        take(
            Exchanger::exchange($this->exchange),
            function (Exchange $exchangeInstance) {
                $this->prices = [];
                $this->times = [];
                foreach ($this->items as $item) {
                    $price = $exchangeInstance->createPrice($item);
                    $this->prices[] = $price->getPrice();
                    $this->times[] = $price->getOpenTime();
                }
            }
        );
        $this->count = count($this->items);
    }

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

    public function fillMissingTimes(?int $endTime = null, int $limit = Exchange::PRICE_LIMIT): static
    {
        $expectedOpenTime = $this->interval->findOpenTimeOf($endTime);
        $missingTimes = [];
        $i = $this->count;
        while (--$i >= 0) {
            if ($this->times[$i] != $expectedOpenTime) {
                $missingTimes[$expectedOpenTime] = $i;
                ++$i;
            }
            $expectedOpenTime = $this->interval->getPreviousOpenTimeOfExact($expectedOpenTime);
        }
        $exchangeInstance = Exchanger::exchange($this->exchange);
        foreach ($missingTimes as $time => $index) {
            array_splice(
                $this->items,
                $index + 1,
                0,
                [
                    $exchangeInstance
                        ->createPrice($this->items[$index])
                        ->setTime($time, $this->interval)
                        ->toArray(),
                ]
            );
            array_splice($this->prices, $index + 1, 0, [$this->prices[$index]]);
            array_splice($this->times, $index + 1, 0, [$time]);
        }
        $this->count = count($this->items);
        if ($spliceLength = max(0, $this->count - $limit)) {
            array_splice($this->items, 0, $spliceLength);
            array_splice($this->prices, 0, $spliceLength);
            array_splice($this->times, 0, $spliceLength);
            $this->count = $limit;
        }
        return $this;
    }

    public function push(PriceCollection $priceCollection): static
    {
        if (!($this->exchange === $priceCollection->getExchange()
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
        return new static(
            $this->exchange,
            $this->ticker,
            $this->interval,
            array_slice($this->items, $offset, $length),
        );
    }

    public function items(): array
    {
        return $this->items;
    }

    /**
     * @return string[] Float-valued strings.
     */
    public function prices(): array
    {
        return $this->prices;
    }

    /**
     * @param int $index
     * @return string Float-valued string.
     */
    public function priceAt(int $index = 0): string
    {
        return $this->prices[$index];
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
            : $this->times[$index];
    }

    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return string Float-valued string.
     */
    public function latestPrice(): string
    {
        return $this->priceAt($this->count - 1);
    }

    public function latestTime(): int
    {
        return $this->timeAt($this->count - 1);
    }
}
