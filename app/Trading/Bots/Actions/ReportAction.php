<?php

namespace App\Trading\Bots\Actions;

use App\Trading\Bots\Bot;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingBroadcast;
use App\Trading\Notifications\Telegram\ConsoleNotification;

class ReportAction implements IAction
{
    public function __invoke(Trading $trading, Bot $bot, TradingBroadcast $broadcast): void
    {
        if (($subscribers = $trading->subscribers->load('socials'))->count() > 0) {
            $indication = $broadcast->indication;
            $label = sprintf('%s %s %s', $indication->getAction(), $bot->ticker(), $bot->interval());
            ConsoleNotification::send(
                $subscribers,
                '.' . str_repeat('_', strlen($label) + 2) . '.' . PHP_EOL
                . '| ' . $label . ' |' . PHP_EOL
                . '˙' . str_repeat('‾', strlen($label) + 2) . '˙' . PHP_EOL
                . $bot->reportNow($indication)
            );
        }
    }
}
