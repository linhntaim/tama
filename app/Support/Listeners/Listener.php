<?php

namespace App\Support\Listeners;

use App\Support\ClassTrait;
use App\Support\Facades\Artisan;
use App\Support\Events\Event;
use App\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class Listener
{
    use ClassTrait;

    /**
     * @param Event $event
     * @return void
     */
    protected function handleBefore($event)
    {
    }

    /**
     * @param Event $event
     * @return void
     */
    protected function handleAfter($event)
    {
    }

    /**
     * @param Event $event
     * @return void
     */
    protected abstract function handling($event);

    /**
     * @param Event $event
     * @return void
     */
    final public function handle($event)
    {
        if (App::runningSolelyInConsole()) {
            if (($runningCommand = Artisan::lastRunningCommand())
                && count($settings = $runningCommand->settings())) {
                $event->setForcedInternalSettings($settings);
            }
        }
        $event->withInternalSettings(function () use ($event) {
            Log::info(sprintf('Listener [%s] started.', $this->className()));
            $this->handleBefore($event);
            $this->handling($event);
            $this->handleAfter($event);
            Log::info(sprintf('Listener [%s] ended.', $this->className()));
        });
    }

    /**
     * @param Event $event
     * @param Throwable $e
     * @return void
     */
    public function failed($event, Throwable $e)
    {
    }
}