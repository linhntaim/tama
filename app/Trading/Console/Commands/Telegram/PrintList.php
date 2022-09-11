<?php

namespace App\Trading\Console\Commands\Telegram;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait PrintList
{
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
