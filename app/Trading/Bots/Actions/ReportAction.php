<?php

namespace App\Trading\Bots\Actions;

use App\Trading\Bots\Bot;
use App\Trading\Bots\Data\Indication;
use App\Trading\Models\Trading;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use Psr\SimpleCache\InvalidArgumentException;

class ReportAction implements IAction
{
    /**
     * @throws InvalidArgumentException
     */
    public function __invoke(Trading $trading, Bot $bot, Indication $indication): void
    {
        $label = sprintf('%s %s %s', $indication->getAction(), $bot->ticker(), $bot->interval());
        ConsoleNotification::send(
            $trading->subscribers,
            '.' . str_repeat('_', strlen($label) + 2) . '.' . PHP_EOL
            . '| ' . $label . ' |' . PHP_EOL
            . '˙' . str_repeat('‾', strlen($label) + 2) . '˙' . PHP_EOL
            . $bot->reportNow($indication)
        );
    }
}
