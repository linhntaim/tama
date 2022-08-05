<?php

namespace App\Trading\Bots;

use App\Trading\Bots\Reporters\IReport;
use App\Trading\Bots\Reporters\PlainTextReporter;
use Illuminate\Support\Collection;

class BotReporter implements IReport
{
    protected IReport $default;

    /**
     * @var array<string, IReport>
     */
    protected array $map;

    /**
     * @param array<string, IReport> $map
     * @param IReport|null $default
     */
    public function __construct(array $map = [], ?IReport $default = null)
    {
        $this->map = array_merge([
            OscillatingBot::class => new OscillatingBotReporter(),
        ], $map);
        $this->default = $default ?? new PlainTextReporter();
    }

    protected function reporter(Bot $bot): IReport
    {
        return $this->map[$bot::class] ?? $this->default;
    }

    public function report(Bot $bot, Collection $indications): string
    {
        return $this->reporter($bot)->report($bot, $indications);
    }
}
