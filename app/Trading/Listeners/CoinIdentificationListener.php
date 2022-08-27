<?php

namespace App\Trading\Listeners;

use App\Support\Events\Event;
use App\Support\Listeners\Listener;
use App\Trading\Events\CoinIdentificationEvent;

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
