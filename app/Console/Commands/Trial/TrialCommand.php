<?php

namespace App\Console\Commands\Trial;

use App\Support\Console\Commands\Command;
use App\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class TrialCommand extends Command
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
