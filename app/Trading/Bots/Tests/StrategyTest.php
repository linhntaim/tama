<?php

namespace App\Trading\Bots\Tests;

use App\Models\User;
use App\Support\Client\DateTimer;
use App\Trading\Bots\Bot;
use App\Trading\Bots\BotFactory;
use App\Trading\Bots\Exchanges\Binance\Binance;
use App\Trading\Bots\Exchanges\Interval;
use App\Trading\Bots\Oscillators\RsiOscillator;
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
        protected string $baseAmount = '0.0',
        protected string $quoteAmount = '500.0',
        protected float  $buyRisk = 0.0,
        protected float  $sellRisk = 0.0,
        string           $buyBotName = 'oscillating_bot',
        array            $buyBotOptions = [
            'exchange' => Binance::NAME,
            'ticker' => Binance::DEFAULT_TICKER,
            'interval' => Binance::DEFAULT_INTERVAL,
            'oscillator' => [
                'name' => RsiOscillator::NAME,
            ],
        ],
        ?string          $sellBotName = null,
        ?array           $sellBotOptions = null,
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

    protected function baseAmount(): string
    {
        return with(0, function (string $amount): string {
            $this->swaps->each(function (SwapTest $swap) use (&$amount) {
                $amount = num_add($amount, $swap->getBaseAmount());
            });
            return $amount;
        });
    }

    protected function quoteAmount(): string
    {
        return with(0, function (string $amount) {
            $this->swaps->each(function (SwapTest $swap) use (&$amount) {
                $amount = num_add($amount, $swap->getQuoteAmount());
            });
            return $amount;
        });
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

    protected function toTime(string $timeString): int
    {
        $number = (int)$timeString;
        if ((string)$number === $timeString) {
            return $number;
        }

        $now = DateTimer::now(null);
        return (match (substr($timeString, -1)) {
            'Y', 'y' => $now->subYears($number),
            'M', 'm' => $now->subMonths($number),
            'W', 'w' => $now->subWeeks($number),
            'D', 'd' => $now->subDays($number),
            'H', 'h' => $now->subHours($number),
            default => DateTimer::parse($timeString)
        })->getTimestamp();
    }

    public function test(string|int|null $startTime = null, string|int|null $endTime = null): ResultTest
    {
        if (is_string($startTime)) {
            $startTime = $this->toTime($startTime);
        }
        if (is_string($endTime)) {
            $endTime = $this->toTime($endTime);
        }

        $now = DateTimer::now(null);
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

        $hasPrices = $buyIntervalLte
            ? $this->sellBot->exchangeConnector()->hasPricesAt(
                $this->sellBot->ticker(),
                $sellInterval,
                $sellStartOpenTime
            )
            : $this->buyBot->exchangeConnector()->hasPricesAt(
                $this->buyBot->ticker(),
                $buyInterval,
                $buyStartOpenTime
            );
        if ($hasPrices !== false) {
            if (is_int($hasPrices)) {
                $buyStartOpenTime = $sellStartOpenTime = $hasPrices;
            }
            if ($buyStartOpenTime < $buyEndOpenTime && $sellStartOpenTime < $sellEndOpenTime) {
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
                                        $indication->getActionTime($this->buyBot->interval()),
                                        $marketOrder->getPrice(),
                                        $marketOrder->getToAmount(),
                                        num_neg($marketOrder->getFromAmount()),
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
                                        $indication,
                                        $indication->getActionTime($this->buyBot->interval()),
                                        $marketOrder->getPrice(),
                                        num_neg($marketOrder->getFromAmount()),
                                        $marketOrder->getToAmount(),
                                        $marketOrder
                                    )
                                );
                            }
                        }
                    }
                    $loopTime += $loopingTime;
                }
            }
        }

        return new ResultTest(
            $this->buyBot->exchange(),
            $this->buyBot->ticker(),
            $this->buyBot->baseSymbol(),
            $this->buyBot->quoteSymbol(),
            $this->buyRisk,
            $this->sellRisk,
            $startTime,
            $endTime,
            $this->swaps
        );
    }

    public function testYearsTillNow(int $years = 1): ResultTest
    {
        return $this->test(sprintf('%dY', $years));
    }

    public function testMonthsTillNow(int $months = 12): ResultTest
    {
        return $this->test(sprintf('%dM', $months));
    }

    public function testWeeksTillNow(int $weeks = 52): ResultTest
    {
        return $this->test(sprintf('%dW', $weeks));
    }

    public function testDaysTillNow(int $days = 365): ResultTest
    {
        return $this->test(sprintf('%dD', $days));
    }

    public function testHoursTillNow(int $hours = 365 * 24): ResultTest
    {
        return $this->test(sprintf('%dH', $hours));
    }
}
