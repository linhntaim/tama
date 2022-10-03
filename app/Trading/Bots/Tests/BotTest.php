<?php

namespace App\Trading\Bots\Tests;

use App\Models\User;
use App\Support\Client\DateTimer;
use App\Trading\Bots\Bot;
use App\Trading\Bots\BotFactory;
use App\Trading\Bots\Exchanges\Binance\Binance;
use App\Trading\Bots\Exchanges\PriceCollection;
use App\Trading\Bots\Oscillators\RsiOscillator;
use App\Trading\Bots\Tests\Data\ResultTest;
use App\Trading\Bots\Tests\Data\SwapTest;
use App\Trading\Bots\Tests\Data\TraderTest;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;

class BotTest
{
    /**
     * @var Bot[]
     */
    protected array $buyBots;

    /**
     * @var Bot[]
     */
    protected array $sellBots;

    /**
     * @var Collection<int, SwapTest>
     */
    protected Collection $swaps;

    /**
     * @param string $baseAmount
     * @param string $quoteAmount
     * @param float $buyRisk
     * @param float $sellRisk
     * @param array[]|array $buyBots
     * @param array[]|array|null $sellBots
     */
    public function __construct(
        protected string $baseAmount = '0.0',
        protected string $quoteAmount = '500.0',
        protected float  $buyRisk = 0.0,
        protected float  $sellRisk = 0.0,
        array            $buyBots = [[
            'name' => 'oscillating_bot',
            'options' => [
                'exchange' => Binance::NAME,
                'ticker' => Binance::DEFAULT_TICKER,
                'interval' => Binance::DEFAULT_INTERVAL,
                'oscillator' => [
                    'name' => RsiOscillator::NAME,
                ],
            ],
        ]],
        ?array           $sellBots = null,
    )
    {
        $map = static function (array $bot) {
            return take(
                BotFactory::create($bot['name'], $bot['options']),
                static fn(Bot $bot) => $bot->useFakeExchangeConnector()
            );
        };
        $this->buyBots = array_map($map, $buyBots);
        $this->sellBots = is_null($sellBots) ? $this->buyBots : array_map($map, $sellBots);
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

    /**
     * @param int $startTime
     * @param int $endTime
     * @return Collection<int, TraderTest>
     */
    protected function prepareTraders(int $startTime, int $endTime): Collection
    {
        $map = function (Bot $bot, string $action) use ($startTime, $endTime): ?TraderTest {
            if ($bot->exchange() !== $this->buyBots[0]->exchange()
                || (string)$bot->ticker() !== (string)$this->buyBots[0]->ticker()) {
                throw new InvalidArgumentException('Buy and sell bot must have the same exchange and ticker.');
            }

            return transform(
                with(
                    $bot->interval()->findOpenTimeOf($startTime),
                    static function (int $startOpenTime) use ($bot) {
                        $hasPrices = $bot->exchangeConnector()->hasPricesAt(
                            $bot->ticker(),
                            $bot->interval(),
                            $startOpenTime
                        );
                        if ($hasPrices === false) {
                            return null;
                        }
                        if (is_int($hasPrices)) {
                            $startOpenTime = $hasPrices;
                        }
                        return $startOpenTime;
                    }
                ),
                static fn(?int $startOpenTime): ?TraderTest => is_null($startOpenTime)
                || $startOpenTime >= ($endOpenTime = $bot->interval()->findOpenTimeOf($endTime, -1))
                    ? null
                    : new TraderTest($action, $bot, $startOpenTime, $endOpenTime)
            );
        };
        return take(
            collect($this->buyBots)
                ->map(fn(Bot $bot): ?TraderTest => $map($bot, TraderTest::ACTION_BUY))
                ->merge(collect($this->sellBots)->map(fn(Bot $bot): ?TraderTest => $map($bot, TraderTest::ACTION_SELL)))
                ->filter()
                ->sort(fn(TraderTest $t1, TraderTest $t2): int => match ($cmp = $t1->compareStartOpenTime($t2)) {
                    0 => $t1->compareInterval($t2),
                    default => $cmp
                })
                ->values(),
            static function (Collection $traders) {
                if ($traders->count() === 0) {
                    throw new RuntimeException('Cannot fetch prices while trading.');
                }
            }
        );
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

        $traders = $this->prepareTraders($startTime, $endTime);

        $startTrader = $traders->first();
        with($startTrader->getPriceCollector()->get(false), fn(PriceCollection $priceCollection) => $this->swaps->push(
            new SwapTest(
                null,
                $priceCollection->latestTime(),
                $priceCollection->latestPrice(),
                $this->baseAmount,
                $this->quoteAmount,
                null
            )
        ));

        $fakeUser = new User();
        $loopOpenTime = $startTrader->getStartOpenTime();
        $loopEndOpenTime = $startTrader->getEndOpenTime();
        $loopingTime = $traders->reduce(function (?int $result, TraderTest $trader): int {
            return $trader->getBot()->interval()->gcd($result);
        });
        while ($loopOpenTime <= $loopEndOpenTime) {
            $traders->first(function (TraderTest $trader) use ($fakeUser, $loopOpenTime) {
                $bot = $trader->getBot();
                if ($loopOpenTime >= $trader->getStartOpenTime()
                    && $bot->interval()->isExact($loopOpenTime)
                    && !is_null($priceCollection = $trader->getPriceCollector()->get())
                    && !is_null($indication = $bot->indicatingNow($priceCollection))) {
                    $bot->exchangeConnector()->setTickerPrice($bot->ticker(), $indication->getPrice());
                    if ($trader->isBuy()) {
                        if (!is_null($marketOrder = $bot->tryToBuyNow(
                            $fakeUser,
                            $this->quoteAmount(),
                            $this->buyRisk,
                            $indication
                        ))) {
                            $this->swaps->push(
                                new SwapTest(
                                    $indication,
                                    $indication->getActionTime($bot->interval()),
                                    $marketOrder->getPrice(),
                                    $marketOrder->getToAmount(),
                                    num_neg($marketOrder->getFromAmount()),
                                    $marketOrder
                                )
                            );
                            return true;
                        }
                    }
                    elseif (!is_null($marketOrder = $bot->tryToSellNow(
                        $fakeUser,
                        $this->baseAmount(),
                        $this->sellRisk,
                        $indication
                    ))) {
                        $this->swaps->push(
                            new SwapTest(
                                $indication,
                                $indication->getActionTime($bot->interval()),
                                $marketOrder->getPrice(),
                                num_neg($marketOrder->getFromAmount()),
                                $marketOrder->getToAmount(),
                                $marketOrder
                            )
                        );
                        return true;
                    }
                }
                return false;
            });
            $loopOpenTime += $loopingTime;
        }

        return new ResultTest(
            $this->buyBots[0]->exchange(),
            $this->buyBots[0]->ticker(),
            $this->buyBots[0]->baseSymbol(),
            $this->buyBots[0]->quoteSymbol(),
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
