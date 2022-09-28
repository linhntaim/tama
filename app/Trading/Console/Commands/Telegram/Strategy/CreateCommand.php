<?php

namespace App\Trading\Console\Commands\Telegram\Strategy;

use App\Models\User;
use App\Support\Client\DateTimer;
use App\Support\Database\Concerns\DatabaseTransaction;
use App\Support\Models\QueryValues\HasValueWithQuery;
use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Console\Commands\Telegram\Command;
use App\Trading\Console\Commands\Telegram\InteractsWithPriceStream;
use App\Trading\Console\Commands\Telegram\InteractsWithTradings;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingStrategy;
use App\Trading\Models\TradingStrategyProvider;
use App\Trading\Models\TradingSwap;
use App\Trading\Models\TradingSwapProvider;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class CreateCommand extends Command
{
    use DatabaseTransaction, InteractsWithTradings, InteractsWithPriceStream;

    public $signature = '{buy_trading_ids} {sell_trading_ids?} {--base-amount=0.0} {--quote-amount=500.0} {--buy_risk=0.0} {--sell_risk=0.0}';

    protected $description = 'Create a strategy.';

    protected string $baseAmount;

    protected string $quoteAmount;

    protected string $buyRisk;

    protected string $sellRisk;

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
        if ($this->validateInputs()
            && ($user = $this->validateCreatingUser()) !== false
            && ($tradings = $this->validateTradings()) !== false) {
            [$buyTradings, $sellTradings] = $tradings;
            if ($this->hasStrategy($user, $buyTradings, $sellTradings)) {
                $this->sendConsoleNotification('The strategy has already existed.');
            }
            else {
                $strategy = $this->createStrategy($user, $buyTradings, $sellTradings);
                transform($strategy->firstSwap, function (TradingSwap $swap) use ($strategy, $buyTradings, $sellTradings) {
                    $firstTrading = $buyTradings->first();
                    $this->sendConsoleNotification(
                        implode(PHP_EOL, [
                            sprintf('The strategy {#%d} has been created.', $strategy->id),
                            sprintf('- Buy (risk=%s):', $strategy->buy_risk),
                            ...$buyTradings->map(function (Trading $trading) {
                                return sprintf('  + {#%d:%s}', $trading->id, $trading->slug);
                            })->all(),
                            sprintf('- Sell (risk=%s):', $strategy->sell_risk),
                            ...$sellTradings->map(function (Trading $trading) {
                                return sprintf('  + {#%d:%s}', $trading->id, $trading->slug);
                            })->all(),
                            sprintf(
                                '- Starting amount: %s %s + %s %s ~ %s %s',
                                num_trim($swap->base_amount),
                                $firstTrading->base_symbol,
                                num_trim($swap->quote_amount),
                                $firstTrading->quote_symbol,
                                num_trim($swap->equivalentQuoteAmount),
                                $firstTrading->quote_symbol,
                            ),
                        ])
                    );
                });
            }
        }
        return $this->exitSuccess();
    }

    protected function hasStrategy(User $user, Collection $buyTradings, Collection $sellTradings): bool
    {
        $trading = new Trading();
        return (new TradingStrategyProvider())->has([
            'user_id' => $user->id,
            'type' => TradingStrategy::TYPE_FAKE,
            'buyTradings' => new HasValueWithQuery(function ($query) use ($trading, $buyTradings) {
                $query->whereIn($trading->qualifyColumn('id'), $buyTradings->pluck('id')->all());
            }, '=', $buyTradings->count()),
            'sellTradings' => new HasValueWithQuery(function ($query) use ($trading, $sellTradings) {
                $query->whereIn($trading->qualifyColumn('id'), $sellTradings->pluck('id')->all());
            }, '=', $sellTradings->count()),
        ]);
    }

    /**
     * @throws Throwable
     */
    protected function createStrategy(User $user, Collection $buyTradings, Collection $sellTradings): TradingStrategy
    {
        $this->transactionStart();
        try {
            $strategy = take(
                (new TradingStrategyProvider())->createWithAttributes([
                    'user_id' => $user->id,
                    'buy_risk' => $this->buyRisk(),
                    'sell_risk' => $this->sellRisk(),
                    'type' => TradingStrategy::TYPE_FAKE,
                    'status' => TradingStrategy::STATUS_ACTIVE,
                ]),
                function (TradingStrategy $strategy) use ($buyTradings, $sellTradings) {
                    $firstTrading = $buyTradings->first();
                    $strategy->setRelation('orderedSwaps', new Collection([
                        (new TradingSwapProvider())->createWithAttributes([
                            'trading_strategy_id' => $strategy->id,
                            'price' => Exchanger::connector($firstTrading->exchange)->tickerPrice($firstTrading->ticker),
                            'time' => DateTimer::databaseNow(null),
                            'base_amount' => $this->baseAmount(),
                            'quote_amount' => $this->quoteAmount(),
                        ]),
                    ]));
                    $strategy->buyTradings()->attach($buyTradings->pluck('id')->all());
                    $strategy->sellTradings()->attach($sellTradings->pluck('id')->all());
                }
            );
            foreach ($buyTradings->merge($sellTradings) as $trading) {
                $this->subscribePriceStream($trading);
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
