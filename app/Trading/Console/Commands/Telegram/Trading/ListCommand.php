<?php

namespace App\Trading\Console\Commands\Telegram\Trading;

use App\Support\Models\QueryValues\LikeValue;
use App\Trading\Console\Commands\Telegram\Command;
use App\Trading\Console\Commands\Telegram\FindUser;
use App\Trading\Console\Commands\Telegram\PrintList;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;

class ListCommand extends Command
{
    use PrintList;

    public $signature = '{--q= : The keyword for searching.} {--page=1}';

    protected $description = 'List all tradings.';

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
            $this->printTradingsBySubscriber()
        );
        return $this->exitSuccess();
    }

    protected function printTradingsBySubscriber(): string
    {
        return $this->printList(
            (new TradingProvider())->pagination(array_filter([
                'slug' => is_null($keyword = $this->keyword()) ? null : LikeValue::create($keyword),
            ]), 10, $this->page()),
            fn(Trading $trading): string => sprintf('#%s:%s', $trading->id, $trading->slug),
            'No tradings.',
            'Tradings:'
        );
    }
}