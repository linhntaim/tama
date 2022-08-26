<?php

namespace App\Listeners;

use App\Events\CoinIdentificationEvent;
use App\Support\Events\Event;
use App\Support\Listeners\Listener;

class CoinIdentificationListener extends Listener
{
    /**
     * @param CoinIdentificationEvent $event
     * @return void
     */
    protected function handling(Event $event): void
    {
        echo $event->symbol . PHP_EOL;
    }
}
