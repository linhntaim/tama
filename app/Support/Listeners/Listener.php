<?php

namespace App\Support\Listeners;

use App\Support\ClassTrait;
use App\Support\Events\Event;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class Listener
{
    use ClassTrait;

    protected function handleBefore($event)
    {
    }

    protected function handleAfter($event)
    {
    }

    protected abstract function handling($event);

    public function handle(Event $event)
    {
        Log::info(sprintf('Listener [%s] started.', $this->className()));
        $this->handleBefore($event);
        $this->handling($event);
        $this->handleAfter($event);
        Log::info(sprintf('Listener [%s] ended.', $this->className()));
    }

    public function failed($event, Throwable $e)
    {
    }
}