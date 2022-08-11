<?php

namespace App\Trading\Console\Commands\Orchestration;

use App\Support\Console\Commands\Command;
use App\Trading\Bots\Actions\ReportAction;
use App\Trading\Bots\Orchestrators\Orchestrator;

class OriginCommand extends Command
{
    protected function handling(): int
    {
        (new Orchestrator([new ReportAction()]))->proceed();
        return $this->exitSuccess();
    }
}
