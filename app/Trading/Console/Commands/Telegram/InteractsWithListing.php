<?php

namespace App\Trading\Console\Commands\Telegram;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait InteractsWithListing
{
    protected function keyword(): ?string
    {
        return $this->option('q');
    }

    protected function page(): int
    {
        return with((int)($this->option('page') ?? 1), static fn($page) => $page <= 0 ? 1 : $page);
    }

    protected function printList(LengthAwarePaginator $list, Closure $printItem, $emptyText = 'No items.', $headlineText = 'Items:'): string
    {
        if ($list->count() === 0) {
            return $emptyText;
        }
        $lines = [$headlineText];
        $lines[] = str_repeat('-', 25);
        foreach ($list as $index => $item) {
            $lines[] = $printItem($item, $index);
        }
        $lines[] = str_repeat('-', 25);
        $lines[] = sprintf('Page: %s / %s', $list->currentPage(), $list->lastPage());
        return implode(PHP_EOL, $lines);
    }
}
