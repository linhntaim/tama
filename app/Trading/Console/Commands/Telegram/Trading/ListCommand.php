<?php

namespace App\Trading\Console\Commands\Telegram\Trading;

use App\Support\Models\QueryValues\LikeValue;
use App\Trading\Console\Commands\Telegram\Command;
use App\Trading\Console\Commands\Telegram\InteractsWithListing;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;

class ListCommand extends Command
{
    use InteractsWithListing;

    public $signature = '{--q= : The keyword for searching.} {--page=1}';

    protected $description = 'List all tradings.';

    protected function handling(): int
    {
        $this->sendConsoleNotification($this->printTradingsBySubscriber());
        return $this->exitSuccess();
    }

    protected function printTradingsBySubscriber(): string
    {
        return $this->printList(
            (new TradingProvider())->pagination(array_filter([
                'slug' => is_null($keyword = $this->keyword()) ? null : new LikeValue($keyword),
            ]), 10, $this->page()),
            fn(Trading $trading): string => sprintf('#%s:%s', $trading->id, $trading->slug),
            'No tradings.',
            'Tradings:'
        );
    }
}