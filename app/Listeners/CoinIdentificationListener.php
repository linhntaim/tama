<?php

namespace App\Listeners;

use App\Events\CoinIdentificationEvent;
use App\Support\Listeners\Listener;

class CoinIdentificationListener extends Listener
{
    /**
     * @param CoinIdentificationEvent $event
     */
    protected function handling($event)
    {
        echo $event->symbol . PHP_EOL;
    }
}