<?php

namespace App\Trading\Console\Commands\Telegram\Strategy;

use App\Models\User;
use App\Support\Client\DateTimer;
use App\Support\Database\Concerns\DatabaseTransaction;
use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Console\Commands\Telegram\Command;
use App\Trading\Console\Commands\Telegram\InteractsWithPriceStream;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use App\Trading\Models\TradingStrategy;
use App\Trading\Models\TradingStrategyProvider;
use App\Trading\Models\TradingSwap;
use App\Trading\Models\TradingSwapProvider;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Throwable;

class CreateCommand extends Command
{
    use DatabaseTransaction, InteractsWithPriceStream;

    public $signature = '{buy_trading_id} {sell_trading_id?} {--base-amount=0.0} {--quote-amount=500.0} {--buy_risk=0.0} {--sell_risk=0.0}';

    protected $description = 'Create a strategy.';

    protected string $baseAmount;

    protected string $quoteAmount;

    protected string $buyRisk;

    protected string $sellRisk;

    protected function buyTradingId(): int
    {
        return $this->argument('buy_trading_id');
    }

    protected function sellTradingId(): ?int
    {
        return $this->argument('buy_trading_id');
    }

    protected function baseAmount(): string
    {
        return $this->baseAmount ?? $this->baseAmount = $this->option('base-amount');
    }

    protected function quoteAmount(): string
    {
        return $this->quoteAmount ?? $this->quoteAmount = $this->option('quote-amount');
    }

    protected function buyRisk(): float
    {
        return $this->buyRisk ?? $this->buyRisk = $this->option('buy_risk');
    }

    protected function sellRisk(): float
    {
        return $this->sellRisk ?? $this->sellRisk = $this->option('sell_risk');
    }

    protected function validateInputs(): bool
    {
        if (num_lt($this->baseAmount(), 0)
            || num_lt($this->quoteAmount(), 0)
            || num_lt($this->buyRisk(), 0)
            || num_lt($this->sellRisk(), 0)
            || (num_eq($this->baseAmount(), 0) && num_eq($this->quoteAmount(), 0))) {
            $this->sendConsoleNotification('Invalid argument(s).');
            return false;
        }
        return true;
    }

    /**
     * @throws Throwable
     */
    protected function handling(): int
    {
        if ($this->validateInputs() && ($user = $this->validateCreatingUser()) !== false) {
            [$buyTrading, $sellTrading] = $this->getTradings();
            $strategyProvider = new TradingStrategyProvider();
            if ($strategyProvider->has([
                'user_id' => $user->id,
                'buy_trading_id' => $buyTrading->id,
                'sell_trading_id' => $sellTrading->id,
                'type' => TradingStrategy::TYPE_FAKE,
            ])) {
                $this->sendConsoleNotification('The strategy has already existed.');
            }
            else {
                $strategy = $this->createStrategy($user, $buyTrading, $sellTrading);
                transform($strategy->firstSwap, function (TradingSwap $swap) use ($strategy, $buyTrading, $sellTrading) {
                    $this->sendConsoleNotification(
                        implode(PHP_EOL, [
                            sprintf('The strategy {#%d} has been created.', $strategy->id),
                            sprintf('- Buy: {#%d:%s} risk=%s', $buyTrading->id, $buyTrading->slug, $strategy->buy_risk),
                            sprintf('- Sell: {#%d:%s} risk=%s', $sellTrading->id, $sellTrading->slug, $strategy->sell_risk),
                            sprintf(
                                '- Starting amount: %s %s + %s %s ~ %s %s',
                                num_trim($swap->base_amount),
                                $strategy->buyTrading->base_symbol,
                                num_trim($swap->quote_amount),
                                $strategy->buyTrading->quote_symbol,
                                num_trim($swap->equivalentQuoteAmount),
                                $strategy->buyTrading->quote_symbol,
                            ),
                        ])
                    );
                });
            }
        }
        return $this->exitSuccess();
    }

    /**
     * @return Trading[]
     */
    protected function getTradings(): array
    {
        $tradingProvider = new TradingProvider();
        $buyTrading = $tradingProvider->firstByKey($this->buyTradingId());
        $sellTrading = is_null($sellTradingId = $this->sellTradingId())
            ? $buyTrading : $tradingProvider->firstByKey($sellTradingId);

        if ($buyTrading->exchange !== $sellTrading->exchange
            || $buyTrading->ticker !== $sellTrading->ticker) {
            throw new InvalidArgumentException('Buy and sell trading must have the same exchange and ticker');
        }

        return [$buyTrading, $sellTrading];
    }

    /**
     * @throws Throwable
     */
    protected function createStrategy(User $user, Trading $buyTrading, Trading $sellTrading): TradingStrategy
    {
        $this->transactionStart();
        try {
            $strategy = take(
                (new TradingStrategyProvider())->createWithAttributes([
                    'user_id' => $user->id,
                    'buy_trading_id' => $buyTrading->id,
                    'sell_trading_id' => $sellTrading->id,
                    'buy_risk' => $this->buyRisk(),
                    'sell_risk' => $this->sellRisk(),
                    'type' => TradingStrategy::TYPE_FAKE,
                    'status' => TradingStrategy::STATUS_ACTIVE,
                ]),
                function (TradingStrategy $strategy) use ($buyTrading) {
                    $strategy->setRelation('orderedSwaps', new Collection([
                        (new TradingSwapProvider())->createWithAttributes([
                            'trading_strategy_id' => $strategy->id,
                            'price' => Exchanger::connector($buyTrading->exchange)->tickerPrice($buyTrading->ticker),
                            'time' => DateTimer::databaseNow(null),
                            'base_amount' => $this->baseAmount(),
                            'quote_amount' => $this->quoteAmount(),
                        ]),
                    ]));
                }
            );
            $this->subscribePriceStream($buyTrading);
            if ($sellTrading->id !== $buyTrading->id) {
                $this->subscribePriceStream($sellTrading);
            }
            $this->transactionComplete();
            return $strategy;
        }
        catch (Throwable $exception) {
            $this->transactionAbort();
            throw $exception;
        }
    }
}
