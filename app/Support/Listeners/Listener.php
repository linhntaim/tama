<?php

namespace App\Support\Listeners;

use App\Support\Concerns\ClassHelper;
use App\Support\Events\Event;
use App\Support\Facades\App;
use App\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class Listener
{
    use ClassHelper;

    protected function handleBefore(Event $event): void
    {
    }

    protected function handleAfter(Event $event): void
    {
    }

    abstract protected function handling(Event $event): void;

    final public function handle(Event $event): void
    {
        if (App::runningSolelyInConsole()
            && !is_null($runningCommand = Artisan::lastRunningCommand())
            && count($settings = $runningCommand->settings())) {
            $event->setForcedInternalSettings($settings);
        }
        $event->withInternalSettings(function () use ($event) {
            Log::info(sprintf('Listener [%s] started.', $this->className()));
            $this->handleBefore($event);
            $this->handling($event);
            $this->handleAfter($event);
            Log::info(sprintf('Listener [%s] ended.', $this->className()));
        });
    }

    public function failed(Event $event, Throwable $e): void
    {
    }
}
