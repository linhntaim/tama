<?php

namespace App\Trading\Console\Commands\Telegram\Trading;

use App\Models\User;
use App\Trading\Console\Commands\Telegram\Command;
use App\Trading\Console\Commands\Telegram\InteractsWithListing;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;

class SubscriptionCommand extends Command
{
    use InteractsWithListing;

    public $signature = '{--q= : The keyword for searching.} {--page=1}';

    protected $description = 'List all trading subscriptions.';

    protected function handling(): int
    {
        if (($user = $this->validateFindingUser()) !== false) {
            $this->sendConsoleNotification($this->printTradingsBySubscriber($user));
        }
        return $this->exitSuccess();
    }

    protected function printTradingsBySubscriber(User $user): string
    {
        return $this->printList(
            (new TradingProvider())->paginationByUser($user, $this->keyword(), 10, $this->page()),
            fn(Trading $trading): string => sprintf('#%s:%s', $trading->id, $trading->slug),
            'No subscriptions.',
            'Trading subscriptions:'
        );
    }
}