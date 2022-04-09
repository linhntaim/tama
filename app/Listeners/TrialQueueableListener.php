<?php

namespace App\Listeners;

use App\Events\TrialEvent;
use App\Support\App;
use App\Support\Listeners\QueueableListener;
use Illuminate\Support\Facades\Log;

class TrialQueueableListener extends QueueableListener
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