<?php

namespace App\Listeners\Trial;

use App\Events\Trial\Event as TrialEvent;
use App\Support\Events\Event;
use App\Support\Facades\App;
use App\Support\Listeners\Listener as BaseListener;
use Illuminate\Support\Facades\Log;

class Listener extends BaseListener
{
    /**
     * @param Event $event
     * @return void
     */
    protected function handling(Event $event): void
    {
        Log::info($date = $event->date());
        if (App::runningSolelyInConsole()) {
            echo $date . PHP_EOL;
        }
    }
}
