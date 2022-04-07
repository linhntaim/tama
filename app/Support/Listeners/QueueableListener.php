<?php

namespace App\Support\Listeners;

use App\Support\App;
use App\Support\Bus\Queueable;
use App\Support\Client\InternalSettingsTrait;
use App\Support\Console\Artisan;
use App\Support\Events\Event;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class QueueableListener extends Listener implements ShouldQueue
{
    use Queueable, InternalSettingsTrait;

    public function __construct()
    {
        $this->captureCurrentSettings();
    }

    public function handle(Event $event)
    {
        if (App::runningSolelyInConsole()) {
            if ($runningCommand = Artisan::lastRunningCommand()) {
                $this->setForcedInternalSettings($runningCommand->settings());
            }
        }
        $this->withInternalSettings(function () use ($event) {
            parent::handle($event);
        });
    }
}