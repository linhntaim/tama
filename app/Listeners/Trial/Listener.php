<?php

namespace App\Listeners\Trial;

use App\Events\Trial\Event as TrialEvent;
use App\Support\Facades\App;
use App\Support\Listeners\Listener as BaseListener;
use Illuminate\Support\Facades\Log;

class Listener extends BaseListener
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
