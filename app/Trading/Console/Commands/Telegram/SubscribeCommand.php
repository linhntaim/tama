<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Models\User;
use App\Support\Client\DateTimer;
use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Bots\Exchanges\Ticker;
use App\Trading\Models\Trading;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;
use App\Trading\Trader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class SubscribeCommand extends Command
{
    use CreateUser, CreateTrading;

    public $signature = '{--bot=oscillating_bot} {--exchange=binance} {--ticker=BTCUSDT} {--interval=1d} {--bot-options=}';

    protected $description = 'Subscribe a trading.';

    protected function handling(): int
    {
        if (!Exchanger::available($this->exchange())) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                sprintf('Subscription for the exchange "%s" was not supported/enabled.', $this->exchange())
            );
        }
        elseif (in_array($this->interval(), [
            Trader::INTERVAL_1_MINUTE,
            Trader::INTERVAL_3_MINUTES,
            Trader::INTERVAL_5_MINUTES,
        ], true)) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                sprintf('Subscription for the interval "%s" was not supported/enabled.', $this->interval())
            );
        }
        elseif (is_null($user = $this->createUserFromTelegram())) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                'Subscription was not supported.'
            );
        }
        else {
            $redis = Redis::connection(trading_cfg_redis_pubsub_connection());
            if ($this->ticker()[0] === '*') {
                $tickers = $this->fetchTickers();
                foreach ($tickers as $ticker) {
                    $this->subscribe($user, $this->createTrading([
                        'ticker' => $ticker->getSymbol(),
                        'base_symbol' => $ticker->getBaseSymbol(),
                        'quote_symbol' => $ticker->getQuoteSymbol(),
                        'safe_ticker' => true,
                    ]), $redis);
                }
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    sprintf('Subscriptions to %s trading(s) were created successfully.', $tickers->count())
                );
            }
            else {
                $this->subscribe($user, $trading = $this->createTrading(), $redis);
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    sprintf('Subscription to the trading {#%s:%s} was created successfully.', $trading->id, $trading->slug)
                );
            }
        }
        return $this->exitSuccess();
    }

    /**
     * @return Collection<int, Ticker>
     */
    protected function fetchTickers(): Collection
    {
        return Exchanger::connector($this->exchange())->availableTickers($this->ticker());
    }

    protected function subscribe(User $user, Trading $trading, $redis): void
    {
        $trading->subscribers()->syncWithoutDetaching([
            $user->id => [
                'subscribed_at' => DateTimer::databaseNow(),
            ],
        ]);
        $redis->publish('price-stream:subscribe', json_encode_readable([
            'exchange' => $trading->exchange,
            'ticker' => $trading->ticker,
            'interval' => $trading->interval,
        ]));
    }
}
