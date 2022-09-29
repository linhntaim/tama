<?php

namespace App\Trading\Bots\Tests\Data;

use App\Trading\Bots\Exchanges\ConnectorInterface;
use App\Trading\Bots\Exchanges\Exchange;
use App\Trading\Bots\Exchanges\Interval;
use App\Trading\Bots\Exchanges\PriceCollection;

class PriceCollectorTest
{
    protected PriceCollection $priceCollection;

    protected int $chunkEndIndex;

    protected int $moreOpenTime;

    protected int $moreTotal;

    public function __construct(
        protected ConnectorInterface $connector,
        protected string             $ticker,
        protected Interval           $interval,
        int                          $startOpenTime,
        int                          $endOpenTime,
    )
    {
        $this->priceCollection = $this->connector->recentPricesAt(
            $this->ticker,
            $this->interval,
            $startOpenTime
        );
        $this->chunkEndIndex = $this->priceCollection->count() - 1;
        $this->moreOpenTime = $startOpenTime;
        $this->moreTotal = $this->interval->diffIndexOfExact($startOpenTime, $endOpenTime);
    }

    protected function more(): bool
    {
        if ($this->moreTotal === 0) {
            return false;
        }
        $limit = min($this->moreTotal, Exchange::PRICE_LIMIT);
        $this->priceCollection->push(
            $this->connector->recentPricesAt(
                $this->ticker,
                $this->interval,
                $this->moreOpenTime = $this->interval->getNextOpenTimeOfExact($this->moreOpenTime, $limit - 1),
                $limit
            )
        );
        $this->moreTotal -= $limit;
        return true;
    }

    public function get(bool $nextAfter = true): ?PriceCollection
    {
        if ($this->chunkEndIndex === $this->priceCollection->count() && !$this->more()) {
            return null;
        }

        $length = $this->chunkEndIndex + 1;
        return take(
            $length < Exchange::PRICE_LIMIT
                ? $this->priceCollection->slice(0, $length)
                : $this->priceCollection->slice($length - Exchange::PRICE_LIMIT, Exchange::PRICE_LIMIT),
            fn() => $nextAfter ? ++$this->chunkEndIndex : false
        );
    }
}
