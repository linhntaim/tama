<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Models\User;
use App\Models\UserProvider;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionCommand extends Command
{
    protected $description = 'List all trading subscriptions.';

    protected function findUser(): User
    {
        return (new UserProvider())
            ->notStrict()
            ->firstByProvider('telegram', $this->telegramUpdate->chatId());
    }

    protected function handling(): int
    {
        if (!is_null($user = $this->findUser())) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                $this->printTradingsBySubscriber($user)
            );
        }
        return $this->exitSuccess();
    }

    protected function printTradingsBySubscriber(User $user): string
    {
        return $this->printTradings((new TradingProvider())->allBySubscriber($user));
    }

    protected function printTradings(Collection $tradings): string
    {
        if ($tradings->count() == 0) {
            return 'No subscriptions.';
        }
        $lines = ['Trading subscriptions:'];
        foreach ($tradings as $trading) {
            $lines[] = $this->printTrading($trading);
        }
        return implode(PHP_EOL, $lines);
    }

    protected function printTrading(Trading $trading): string
    {
        return sprintf('#%s:%s', $trading->id, $trading->slug);
    }
}
