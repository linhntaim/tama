<?php

namespace App\Trading\Bots\Tests;

use App\Models\User;
use App\Support\Client\DateTimer;
use App\Trading\Bots\Bot;
use App\Trading\Bots\BotFactory;
use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Exchanges\Binance\Binance;
use App\Trading\Bots\Exchanges\Interval;
use App\Trading\Bots\Exchanges\Price;
use App\Trading\Bots\Exchanges\PriceCollection;
use App\Trading\Bots\Oscillators\RsiOscillator;
use App\Trading\Bots\Tests\Data\PriceCollectorTest;
use App\Trading\Bots\Tests\Data\ResultTest;
use App\Trading\Bots\Tests\Data\SwapTest;
use App\Trading\Bots\Tests\Data\TraderTest;
use App\Trading\Trader;
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

            [$startOpenTime, $endOpenTime] = [
                $bot->interval()->findOpenTimeOf($startTime),
                $bot->interval()->findOpenTimeOf($endTime, -1),
            ];
            return $startOpenTime >= $endOpenTime ? null : new TraderTest($action, $bot, $startOpenTime, $endOpenTime);
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
                    throw new RuntimeException('Cannot trade.');
                }
            }
        );
    }

    /**
     * @param Collection<int, TraderTest> $traders
     * @return PriceCollectorTest[]
     */
    protected function preparePriceCollectors(Collection $traders): array
    {
        return $traders
            ->keyBy(function (TraderTest $trader) {
                return (string)$trader->getBot()->interval();
            })
            ->map(function (TraderTest $trader) {
                $bot = $trader->getBot();
                return new PriceCollectorTest(
                    $bot->exchangeConnector(),
                    $bot->ticker(),
                    $bot->interval(),
                    $trader->getStartOpenTime(),
                    $trader->getEndOpenTime()
                );
            })->all();
    }

    /**
     * @param Collection $traders
     * @return int[]
     */
    protected function prepareLoop(Collection $traders): array
    {
        $startTrader = $traders->first();
        return [
            $startTrader->getStartOpenTime(),
            $startTrader->getEndOpenTime(),
            $traders->reduce(function (?int $result, TraderTest $trader): int {
                return $trader->getBot()->interval()->gcd($result);
            }),
        ];
    }

    public function test(string|int|null $startTime = null, string|int|null $endTime = null): ResultTest
    {
        [$startTime, $endTime] = $this->toTimes($startTime, $endTime);

        $traders = $this->prepareTraders($startTime, $endTime);
        $priceCollectors = $this->preparePriceCollectors($traders);
        $fakeUser = new User();

        [$loopOpenTime, $loopEndOpenTime, $loopingTime] = $this->prepareLoop($traders);
        while ($loopOpenTime <= $loopEndOpenTime) {
            $this->testTraders($traders, $priceCollectors, $loopOpenTime, $fakeUser);
            $loopOpenTime += $loopingTime;
        }

        return $this->createResult($startTime, $endTime);
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
     * @param string|int|null $startTime
     * @param string|int|null $endTime
     * @return int[]
     */
    protected function toTimes(string|int|null $startTime = null, string|int|null $endTime = null): array
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

        take($this->buyBots[0], function (Bot $bot) use (&$startTime, $endTime) {
            $price = $bot->exchangeConnector()->hasPriceAt($bot->ticker(), $startTime - 60); // should check price at previous minute
            if ($price instanceof Price) {
                $startTime = max($startTime, $price->getOpenTime());
                if ($startTime >= $endTime) {
                    throw new InvalidArgumentException('Start time must be less than end time.');
                }

                $this->swaps->push(
                    new SwapTest(
                        null,
                        $startTime,
                        $price->getPrice(),
                        $this->baseAmount,
                        $this->quoteAmount,
                        null
                    )
                );
            }
            else {
                throw new InvalidArgumentException('Unable to fetch prices at the time range.');
            }
        });

        return [$startTime, $endTime];
    }

    /**
     * @param Collection $traders
     * @param PriceCollectorTest[] $priceCollectors
     * @param int $loopOpenTime
     * @param User $fakeUser
     * @return void
     */
    protected function testTraders(Collection $traders, array $priceCollectors, int $loopOpenTime, User $fakeUser): void
    {
        $cachedExacts = [];
        $cachedNextAfters = [];
        $cachedIndications = [];
        foreach ($traders as $trader) {
            $bot = $trader->getBot();
            $interval = (string)$bot->interval();
            if ($loopOpenTime >= $trader->getStartOpenTime()
                && ($cachedExacts[$interval] ?? $cachedExacts[$interval] = $bot->interval()->isExact($loopOpenTime))
                && !is_null(
                    $indication = $cachedIndications[$bot->asSlug()]
                        ?? $cachedIndications[$bot->asSlug()] = (static function () use ($priceCollectors, $bot, $interval, &$cachedNextAfters): ?Indication {
                            return is_null(
                                $priceCollection = $priceCollectors[$interval]->get(
                                    $cachedNextAfters[$interval] ?? !($cachedNextAfters[$interval] = false)
                                )
                            )
                                ? null
                                : $bot->indicatingNow($priceCollection);
                        })()
                )) {
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
                }
            }
        }
    }

    protected function createResult(int $startTime, int $endTime): ResultTest
    {
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
