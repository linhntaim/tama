<?php

namespace App\Trading\Bots\Tests\Data;

use App\Trading\Bots\Bot;

class TraderTest
{
    public const ACTION_BUY = 'buy';
    public const ACTION_SELL = 'sell';

    protected PriceCollectorTest $priceCollector;

    public function __construct(
        protected string $action,
        protected Bot    $bot,
        protected int    $startOpenTime,
        protected int    $endOpenTime)
    {
        $this->priceCollector = new PriceCollectorTest(
            $this->bot->exchangeConnector(),
            $this->bot->ticker(),
            $this->bot->interval(),
            $this->startOpenTime,
            $this->endOpenTime
        );
    }

    public function isBuy(): bool
    {
        return $this->action === self::ACTION_BUY;
    }

    public function getBot(): Bot
    {
        return $this->bot;
    }

    public function getStartOpenTime(): int
    {
        return $this->startOpenTime;
    }

    public function getEndOpenTime(): int
    {
        return $this->endOpenTime;
    }

    public function getPriceCollector(): PriceCollectorTest
    {
        return $this->priceCollector;
    }

    public function compareInterval(TraderTest $trader): int
    {
        return $this->bot->interval()->cmp($trader->getBot()->interval());
    }

    public function compareStartOpenTime(TraderTest $trader): int
    {
        $nextStartOpenTime = $this->bot->interval()->getNextOpenTimeOfExact($this->startOpenTime);
        $traderNextStartOpenTime = $trader->getBot()->interval()->getNextOpenTimeOfExact($trader->getStartOpenTime());
        return match (true) {
            $nextStartOpenTime === $traderNextStartOpenTime => 0,
            $nextStartOpenTime > $traderNextStartOpenTime => 1,
            default => -1,
        };
    }
}