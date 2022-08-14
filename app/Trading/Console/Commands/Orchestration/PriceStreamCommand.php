<?php

namespace App\Trading\Console\Commands\Orchestration;

use App\Support\Console\Commands\Command;
use App\Trading\Bots\Orchestrators\PriceStreamOrchestrator;

class PriceStreamCommand extends Command
{
    protected function handling(): int
    {
        (new PriceStreamOrchestrator())->proceed();
        return $this->exitSuccess();
    }
}
