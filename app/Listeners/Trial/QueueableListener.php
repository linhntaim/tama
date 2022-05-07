<?php

namespace App\Listeners\Trial;

use App\Events\Trial\Event as TrialEvent;
use App\Support\Facades\App;
use App\Support\Listeners\QueueableListener as BaseQueueableListener;
use Illuminate\Support\Facades\Log;

class QueueableListener extends BaseQueueableListener
{
    /**
     * @param TrialEvent $event
     * @return void
     */
    protected function handling($event)
    {
        Log::info('Captured: ' . $event->capturedDate());
        Log::info('Real-time: ' . $date = $event->date());
        if (App::runningSolelyInConsole()) {
            echo 'Captured: ' . $event->capturedDate() . PHP_EOL;
            echo 'Real-time: ' . $date . PHP_EOL;
        }
    }
}
