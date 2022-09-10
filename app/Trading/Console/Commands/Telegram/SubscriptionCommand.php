<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Models\User;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubscriptionCommand extends Command
{
    use FindUser, PrintList;

    public $signature = '{--q= : The keyword for searching.} {--page=1}';

    protected $description = 'List all trading subscriptions.';

    protected function keyword(): ?string
    {
        return $this->option('q');
    }

    protected function page(): int
    {
        return with((int)($this->option('page') ?? 1), static fn($page) => $page <= 0 ? 1 : $page);
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
        return $this->printList(
            (new TradingProvider())->paginationBySubscriber($user, $this->keyword(), 10, $this->page()),
            fn(Trading $trading): string => sprintf('#%s:%s', $trading->id, $trading->slug),
            'No subscriptions.',
            'Trading subscriptions:'
        );
    }
}
