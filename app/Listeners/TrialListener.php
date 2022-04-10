<?php

namespace App\Listeners;

use App\Events\TrialEvent;
use App\Support\Facades\App;
use App\Support\Listeners\Listener;
use Illuminate\Support\Facades\Log;

class TrialListener extends Listener
{
    /**
     * @param TrialEvent $event
     * @return void
     */
    protected function handling($event)
    {
        Log::info($date = $event->date());
        if (App::runningSolelyInConsole()) {
            echo $date . PHP_EOL;
        }
    }
}