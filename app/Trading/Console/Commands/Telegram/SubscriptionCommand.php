<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Models\User;
use App\Models\UserProvider;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubscriptionCommand extends Command
{
    public $signature = '{--q= : The keyword for searching.} {--page=1}';

    protected $description = 'List all trading subscriptions.';

    protected function keyword(): ?string
    {
        return $this->option('q');
    }

    protected function page(): int
    {
        return modify((int)($this->option('page') ?? 1), static fn($page) => $page <= 0 ? 1 : $page);
    }

    protected function findUser(): ?User
    {
        return (new UserProvider())
            ->notStrict()
            ->firstByProvider('telegram', $this->telegramUpdate->chatId());
    }

    protected function handling(): int
    {
        ConsoleNotification::send(
            new TelegramUpdateNotifiable($this->telegramUpdate),
            !is_null($user = $this->findUser())
                ? $this->printTradingsBySubscriber($user)
                : 'No subscriptions.'
        );
        return $this->exitSuccess();
    }

    protected function printTradingsBySubscriber(User $user): string
    {
        return $this->printTradings((new TradingProvider())->paginationBySubscriber($user, $this->keyword(), 10, $this->page()));
    }

    protected function printTradings(LengthAwarePaginator $tradings): string
    {
        if ($tradings->count() === 0) {
            return 'No subscriptions.';
        }
        $lines = ['Trading subscriptions:'];
        $lines[] = str_repeat('-', 25);
        foreach ($tradings as $trading) {
            $lines[] = $this->printTrading($trading);
        }
        $lines[] = str_repeat('-', 25);
        $lines[] = sprintf('Page: %s / %s', $tradings->currentPage(), $tradings->lastPage());
        return implode(PHP_EOL, $lines);
    }

    protected function printTrading(Trading $trading): string
    {
        return sprintf('#%s:%s', $trading->id, $trading->slug);
    }
}
