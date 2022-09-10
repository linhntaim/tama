<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Models\User;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Redis;

class UnsubscribeCommand extends Command
{
    use FindUser;

    public $signature = '{id? : The ID or slug of the trading.} {--bot=} {--exchange=} {--ticker=} {--interval=} {--all}';

    protected $description = 'Unsubscribe tradings.';

    protected function id(): ?string
    {
        return $this->argument('id');
    }

    protected function all(): bool
    {
        return $this->option('all');
    }

    protected function bot(): ?string
    {
        return $this->option('bot');
    }

    protected function exchange(): ?string
    {
        return $this->option('exchange');
    }

    protected function ticker(): ?string
    {
        return $this->option('ticker');
    }

    protected function interval(): ?string
    {
        return $this->option('interval');
    }

    protected function findTrading(): ?Trading
    {
        return is_null($id = $this->id()) ? null : (new TradingProvider())
            ->notStrict()
            ->firstByUnique($id);
    }

    protected function findTradings($user): ?Collection
    {
        $conditions = array_filter([
            'bot' => $this->bot(),
            'exchange' => $this->exchange(),
            'ticker' => $this->ticker(),
            'interval' => $this->interval(),
        ]);
        return count($conditions)
            ? (new TradingProvider())->all($conditions + ['subscriber' => $user])
            : null;
    }

    protected function handling(): int
    {
        if (!is_null($user = $this->findUser())) {
            $redis = Redis::connection(trading_cfg_redis_pubsub_connection());
            if (!is_null($trading = $this->findTrading())) {
                $this->unsubscribe($user, $trading, $redis);
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    sprintf('Subscription to the trading {#%s:%s} was removed successfully.', $trading->id, $trading->slug)
                );
            }
            elseif ($this->all()) {
                foreach ($user->tradings as $trading) {
                    $this->unsubscribe($user, $trading, $redis);
                }
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    'Subscriptions to all tradings were removed successfully.'
                );
            }
            elseif (!is_null($tradings = $this->findTradings($user)) && ($count = $tradings->count()) > 0) {
                foreach ($tradings as $trading) {
                    $this->unsubscribe($user, $trading, $redis);
                }
                if ($count === 1) {
                    $trading = $tradings->first();
                    ConsoleNotification::send(
                        new TelegramUpdateNotifiable($this->telegramUpdate),
                        sprintf('Subscription to the trading {#%s:%s} was removed successfully.', $trading->id, $trading->slug)
                    );
                }
                else {
                    ConsoleNotification::send(
                        new TelegramUpdateNotifiable($this->telegramUpdate),
                        sprintf('Subscriptions to %d tradings were removed successfully.', $count)
                    );
                }
            }
            else {
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    'No subscription was removed.'
                );
            }
        }
        return $this->exitSuccess();
    }

    protected function unsubscribe(User $user, Trading $trading, $redis): void
    {
        $trading->subscribers()->detach($user->id);
        if ($trading->subscribers()->count() === 0) {
            $redis->publish('price-stream:unsubscribe', json_encode_readable([
                'exchange' => $trading->exchange,
                'ticker' => $trading->ticker,
                'interval' => $trading->interval,
            ]));
        }
    }
}
