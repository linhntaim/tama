<?php

namespace App\Trading\Bots\Actions;

use App\Trading\Bots\Bot;
use App\Trading\Bots\BotReporter;
use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Reporters\IReport;
use App\Trading\Models\Trading;
use App\Trading\Notifications\Telegram\ConsoleNotification;

class ReportAction implements IAction
{
    /**
     * @param array<string, IReport> $map
     * @param IReport|null $default
     */
    public function __construct(protected array $map = [], protected ?IReport $default = null)
    {
    }

    public function __invoke(Trading $trading, Bot $bot, Indication $indication): void
    {
        $label = sprintf('%s %s BROADCAST', $indication->getAction(), $bot->ticker());
        ConsoleNotification::send(
            $trading->subscribers,
            '.' . str_repeat('_', strlen($label) + 2) . '.' . PHP_EOL
            . '| ' . $label . ' |' . PHP_EOL
            . '˙' . str_repeat('‾', strlen($label) + 2) . '˙' . PHP_EOL
            . (new BotReporter($this->map, $this->default))->report($bot, collect([$indication]))
        );
    }
}
