<?php

namespace App\Trading\Bots\Tests;

use App\Models\User;
use App\Support\Client\DateTimer;
use App\Trading\Bots\Bot;
use App\Trading\Bots\BotFactory;
use App\Trading\Bots\Exchanges\Binance\Binance;
use App\Trading\Bots\Exchanges\Interval;
use App\Trading\Bots\Oscillators\RsiOscillator;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class StrategyTest
{
    protected Bot $buyBot;

    protected Bot $sellBot;

    /**
     * @var Collection<int, SwapTest>
     */
    protected Collection $swaps;

    public function __construct(
        protected float $baseAmount = 0.0,
        protected float $quoteAmount = 500.0,
        protected float $buyRisk = 0.0,
        protected float $sellRisk = 0.0,
        string          $buyBotName = 'oscillating_bot',
        array           $buyBotOptions = [
            'exchange' => Binance::NAME,
            'ticker' => Binance::DEFAULT_TICKER,
            'interval' => Binance::DEFAULT_INTERVAL,
            'oscillator' => [
                'name' => RsiOscillator::NAME,
            ],
        ],
        ?string         $sellBotName = null,
        ?array          $sellBotOptions = null,
    )
    {
        $this->buyBot = tap(
            BotFactory::create($buyBotName, $buyBotOptions),
            static fn(Bot $bot) => $bot->useFakeExchangeConnector()
        );
        $this->sellBot = tap(
            BotFactory::create($sellBotName ?: $buyBotName, $sellBotOptions ?: $buyBotOptions),
            static fn(Bot $bot) => $bot->useFakeExchangeConnector()
        );
        if ($this->buyBot->exchange() !== $this->sellBot->exchange()
            || (string)$this->buyBot->ticker() !== (string)$this->sellBot->ticker()) {
            throw new InvalidArgumentException('Buy and sell bot must have the same exchange and ticker');
        }

        $this->swaps = new Collection();
    }

    protected function baseAmount(): float
    {
        return $this->swaps->sum('base_amount');
    }

    protected function quoteAmount(): float
    {
        return $this->swaps->sum('quote_amount');
    }

    protected function calculateOpenTime(Interval $interval, int $time): int
    {
        return with(
            $interval->findOpenTimeOf($time),
            static fn(int $openTime) => $openTime === $interval->getLatestOpenTime()
                ? $interval->getPreviousOpenTimeOfExact($openTime)
                : $openTime
        );
    }

    public function test(?int $startTime = null, ?int $endTime = null): ResultTest
    {
        $now = Carbon::now();
        if ((!is_null($startTime) && $startTime > $now->getTimestamp())
            || (!is_null($endTime) && $endTime > $now->getTimestamp())) {
            throw new InvalidArgumentException('Start/End time must be in the past.');
        }
        if ($endTime === null) {
            $endTime = $now->getTimestamp();
        }
        if ($startTime === null) {
            $startTime = $now->subYear()->getTimestamp();
        }

        if ($startTime >= $endTime) {
            throw new InvalidArgumentException('Start time must be less than end time.');
        }

        $buyInterval = $this->buyBot->interval();
        $sellInterval = $this->sellBot->interval();
        $buyIntervalLte = $buyInterval->lte($sellInterval);
        $buyStartOpenTime = $sellStartOpenTime = $this->calculateOpenTime(
            $buyIntervalLte ? $sellInterval : $buyInterval,
            $startTime
        );
        $buyEndOpenTime = $this->calculateOpenTime($buyInterval, $endTime);
        $sellEndOpenTime = $this->calculateOpenTime($sellInterval, $endTime);

        if ($buyStartOpenTime >= $buyEndOpenTime || $sellStartOpenTime >= $sellEndOpenTime) {
            throw new InvalidArgumentException('Start and end time must be in different interval zones.');
        }

        $buyPriceCollector = new PriceCollectorTest(
            $this->buyBot->exchangeConnector(),
            $this->buyBot->ticker(),
            $buyInterval,
            $buyStartOpenTime,
            $buyEndOpenTime,
        );
        $sellPriceCollector = new PriceCollectorTest(
            $this->sellBot->exchangeConnector(),
            $this->sellBot->ticker(),
            $sellInterval,
            $sellStartOpenTime,
            $sellEndOpenTime
        );

        $priceCollection = $buyIntervalLte ? $buyPriceCollector->get(false) : $sellPriceCollector->get(false);
        $this->swaps->push(
            new SwapTest(
                null,
                $priceCollection->latestTime(),
                $priceCollection->latestPrice(),
                $this->baseAmount,
                $this->quoteAmount,
                null
            )
        );

        $fakeUser = new User();
        $loopTime = ($buyInterval->lte($sellInterval) ? $buyInterval : $sellInterval)->getNextOpenTimeOfExact($buyStartOpenTime);
        $loopingTime = $buyInterval->gcd($sellInterval);
        $loopEndTime = $buyIntervalLte
            ? $buyInterval->getNextOpenTimeOfExact($buyEndOpenTime)
            : $sellInterval->getNextOpenTimeOfExact($sellEndOpenTime);
        while ($loopTime <= $loopEndTime) {
            if ($buyInterval->isExact($loopTime)) {
                $priceCollection = $buyPriceCollector->get();
                if (!is_null($indication = $this->buyBot->indicatingNow($priceCollection))) {
                    $this->buyBot->exchangeConnector()->setTickerPrice($this->buyBot->ticker(), $indication->getPrice());
                    if (!is_null($marketOrder = $this->buyBot->tryToBuyNow(
                        $fakeUser,
                        $this->quoteAmount(),
                        $this->buyRisk,
                        $indication
                    ))) {
                        $this->swaps->push(
                            new SwapTest(
                                $indication,
                                $marketOrder->getTime(),
                                $marketOrder->getPrice(),
                                $marketOrder->getToAmount(),
                                -$marketOrder->getFromAmount(),
                                $marketOrder
                            )
                        );
                    }
                }
            }
            if ($sellInterval->isExact($loopTime)) {
                $priceCollection = $sellPriceCollector->get();
                if (!is_null($indication = $this->sellBot->indicatingNow($priceCollection))) {
                    $this->sellBot->exchangeConnector()->setTickerPrice($this->sellBot->ticker(), $indication->getPrice());
                    if (!is_null($marketOrder = $this->sellBot->tryToSellNow(
                        $fakeUser,
                        $this->baseAmount(),
                        $this->sellRisk,
                        $indication
                    ))) {
                        $this->swaps->push(
                            new SwapTest(
                                $marketOrder->getTime(),
                                $marketOrder->getPrice(),
                                -$marketOrder->getFromAmount(),
                                $marketOrder->getToAmount(),
                                $indication
                            )
                        );
                    }
                }
            }
            $loopTime += $loopingTime;
        }

        return new ResultTest(
            $this->buyBot->exchange(),
            $this->buyBot->ticker(),
            $this->buyBot->baseSymbol(),
            $this->buyBot->quoteSymbol(),
            $startTime,
            $endTime,
            $this->swaps
        );
    }

    public function testYearsTillNow(int $years = 1): ResultTest
    {
        $now = DateTimer::now(null);
        return $this->test((clone $now)->subYears($years)->getTimestamp(), $now->getTimestamp());
    }

    public function testDaysTillNow(int $days = 365): ResultTest
    {
        $now = DateTimer::now(null);
        return $this->test((clone $now)->subDays($days)->getTimestamp(), $now->getTimestamp());
    }

    public function testHoursTillNow(int $hours = 365 * 24): ResultTest
    {
        $now = DateTimer::now(null);
        return $this->test((clone $now)->subHours($hours)->getTimestamp(), $now->getTimestamp());
    }
}
