<?php

namespace App\Console\Commands\Trial;

use App\Support\App;
use App\Support\Console\Commands\Command;
use Illuminate\Support\Facades\Log;

class CommandCommand extends Command
{
    protected function handling(): int
    {
        Log::info($date = date_timer()->compound('longDate', ' ', 'longTime'));
        if (App::runningSolelyInConsole()) {
            echo $date . PHP_EOL;
        }
        return $this->exitSuccess();
    }
}