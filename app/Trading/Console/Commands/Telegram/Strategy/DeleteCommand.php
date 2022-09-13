<?php

namespace App\Trading\Console\Commands\Telegram\Strategy;

use App\Models\User;
use App\Trading\Console\Commands\Telegram\Command;
use App\Trading\Console\Commands\Telegram\FindUser;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use App\Trading\Models\TradingStrategy;
use App\Trading\Models\TradingStrategyProvider;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Redis;

class DeleteCommand extends Command
{
    use FindUser;

    public $signature = '{id?} {--all}';

    protected $description = 'Delete a strategy or all.';

    protected function id(): ?string
    {
        return $this->argument('id');
    }

    protected function all(): bool
    {
        return $this->option('all');
    }

    protected function findTradingStrategy(User $user): ?TradingStrategy
    {
        return is_null($id = $this->id()) ? null : (new TradingStrategyProvider())
            ->notStrict()
            ->first([
                'user_id' => $user->id,
                'id' => $id,
            ]);
    }

    /**
     * @param User $user
     * @return Collection<int, TradingStrategy>|null
     */
    protected function findTradingStrategies(User $user): ?Collection
    {
        return (new TradingStrategyProvider())->all(['user_id' => $user->id]);
    }

    protected function handling(): int
    {
        if (!is_null($user = $this->findUser())) {
            $redis = Redis::connection(trading_cfg_redis_pubsub_connection());
            if (!is_null($strategy = $this->findTradingStrategy($user))) {
                $this->deleteStrategy($strategy, $redis);
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    sprintf('Trading strategy {#%s} was removed successfully.', $strategy->id)
                );
            }
            elseif ($this->all()) {
                foreach ($this->findTradingStrategies($user) as $strategy) {
                    $this->deleteStrategy($strategy, $redis);
                }
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    'All trading strategies were removed successfully.'
                );
            }
            else {
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    'No trading strategy was removed.'
                );
            }
        }
        return $this->exitSuccess();
    }

    protected function deleteStrategy(TradingStrategy $strategy, $redis): void
    {
        $buyTrading = $strategy->buyTrading;
        $sellTrading = $strategy->sellTrading;

        $strategy->delete();
        $this->unsubscribe($buyTrading, $redis);
        if ($buyTrading->id !== $sellTrading->id) {
            $this->unsubscribe($sellTrading, $redis);
        }
    }

    protected function unsubscribe(Trading $trading, $redis): void
    {
        if ($trading->subscribers()->count() === 0
            && $trading->buyStrategies()->count() === 0
            && $trading->sellStrategies()->count() === 0) {
            $redis->publish('price-stream:unsubscribe', json_encode_readable([
                'exchange' => $trading->exchange,
                'ticker' => $trading->ticker,
                'interval' => $trading->interval,
            ]));
        }
    }
}