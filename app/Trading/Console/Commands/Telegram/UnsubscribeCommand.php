<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Models\User;
use App\Models\UserProvider;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;

class UnsubscribeCommand extends Command
{
    public $signature = '{id? : The ID or slug of the trading.} {--all}';

    protected $description = 'Unsubscribe a trading.';

    protected function id(): ?string
    {
        return $this->argument('id');
    }

    protected function findTrading(): ?Trading
    {
        return is_null($this->id()) ? null : (new TradingProvider())
            ->notStrict()
            ->firstByUnique($this->id());
    }

    protected function findUser(): ?User
    {
        return (new UserProvider())
            ->notStrict()
            ->firstByProvider('telegram', $this->telegramUpdate->chatId());
    }

    protected function handling(): int
    {
        if (!is_null($user = $this->findUser())) {
            if (!is_null($trading = $this->findTrading())) {
                $trading->subscribers()->detach($user->id);
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    sprintf('Subscription to the trading {%s:%s} was removed successfully.', $trading->id, $trading->slug)
                );
            }
            elseif ($this->option('all')) {
                $user->tradings()->detach();
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    'Subscriptions to all tradings were removed successfully.'
                );
            }
            else {
                $user->tradings()->detach();
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    'No subscription was removed.'
                );
            }
        }
        return $this->exitSuccess();
    }
}
