<?php

namespace App\Trading\Console\Commands\Orchestration;

use App\Support\Console\Commands\Command;
use App\Trading\Bots\Actions\ReportAction;
use App\Trading\Bots\BotOrchestrator;

class BroadcastCommand extends Command
{
    protected function handling(): int
    {
        (new BotOrchestrator())->broadcast([new ReportAction()]);
        return $this->exitSuccess();
    }
}
