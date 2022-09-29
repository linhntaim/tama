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
        if (($users = $trading->users->load('socials'))->count() > 0) {
            $indication = $broadcast->indication;
            $label = sprintf('%s %s %s', $indication->getAction(), $bot->ticker(), $bot->interval());
            ConsoleNotification::send(
                $users,
                implode(PHP_EOL, [
                    $label,
                    str_repeat('‾', strlen($label)),
                    $bot->reportNow($indication),
                ])
            );
        }
    }
}
