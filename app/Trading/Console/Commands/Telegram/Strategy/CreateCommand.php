<?php

namespace App\Trading\Console\Commands\Telegram\Strategy;

use App\Models\User;
use App\Support\Client\DateTimer;
use App\Support\Database\Concerns\DatabaseTransaction;
use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Console\Commands\Telegram\Command;
use App\Trading\Console\Commands\Telegram\CreateUser;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use App\Trading\Models\TradingStrategy;
use App\Trading\Models\TradingStrategyProvider;
use App\Trading\Models\TradingSwapProvider;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Redis;
use InvalidArgumentException;
use Throwable;

class CreateCommand extends Command
{
    use DatabaseTransaction, CreateUser;

    public $signature = '{buy_trading_id} {sell_trading_id?} {--base-amount=0.0} {--quote-amount=500.0} {--buy_risk=0.0} {--sell_risk=0.0}';

    protected function buyTradingId(): int
    {
        return $this->argument('buy_trading_id');
    }

    protected function sellTradingId(): ?int
    {
        return $this->argument('buy_trading_id');
    }

    protected function baseAmount(): float
    {
        return $this->option('base-amount');
    }

    protected function quoteAmount(): float
    {
        return $this->option('quote-amount');
    }

    protected function buyRisk(): float
    {
        return $this->option('buy_risk');
    }

    protected function sellRisk(): float
    {
        return $this->option('sell_risk');
    }

    /**
     * @throws Throwable
     */
    protected function handling(): int
    {
        if (is_null($user = $this->createUserFromTelegram())) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                'Subscription was not supported.'
            );
        }
        else {
            [$buyTrading, $sellTrading] = $this->getTradings();
            $strategyProvider = new TradingStrategyProvider();
            if ($strategyProvider->has([
                'user_id' => $user->id,
                'buy_trading_id' => $buyTrading->id,
                'sell_trading_id' => $sellTrading->id,
                'type' => TradingStrategy::TYPE_FAKE,
            ])) {
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    'The strategy has already existed.'
                );
            }
            else {
                $strategy = $this->createStrategy($user, $buyTrading, $sellTrading);
                $swap = $strategy->orderedSwaps->first();
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    implode(PHP_EOL, [
                        sprintf('The strategy {#%d} has been created.', $strategy->id),
                        sprintf('- Buy: {#%d:%s} risk=%s', $buyTrading->id, $buyTrading->slug, $strategy->buy_risk),
                        sprintf('- Sell: {#%d:%s} risk=%s', $sellTrading->id, $sellTrading->slug, $strategy->sell_risk),
                        sprintf(
                            '- Starting amount: %s%s + %s%s ~ %s%s',
                            num_str($swap->base_amount),
                            $strategy->buyTrading->base_symbol,
                            num_str($swap->quote_amount),
                            $strategy->buyTrading->quote_symbol,
                            num_add($swap->price * $swap->base_amount, $swap->quote_amount),
                            $strategy->buyTrading->quote_symbol,
                        ),
                    ])
                );
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
            $strategy = tap(
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
            tap(Redis::connection(trading_cfg_redis_pubsub_connection()), static function ($redis) use ($buyTrading, $sellTrading) {
                $redis->publish('price-stream:subscribe', json_encode_readable([
                    'exchange' => $buyTrading->exchange,
                    'ticker' => $buyTrading->ticker,
                    'interval' => $buyTrading->interval,
                ]));
                if ($sellTrading->id !== $buyTrading->id) {
                    $redis->publish('price-stream:subscribe', json_encode_readable([
                        'exchange' => $sellTrading->exchange,
                        'ticker' => $sellTrading->ticker,
                        'interval' => $sellTrading->interval,
                    ]));
                }
            });
            $this->transactionComplete();
            return $strategy;
        }
        catch (Throwable $exception) {
            $this->transactionAbort();
            throw $exception;
        }
    }
}
