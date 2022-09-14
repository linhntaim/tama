<?php

namespace App\Support\Events;

use App\Support\Client\Concerns\InternalSettings;
use App\Support\Facades\App;
use App\Support\Facades\Artisan;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class Event
{
    use Dispatchable, InteractsWithSockets, SerializesModels, InternalSettings;

    public function __construct()
    {
        $this->captureCurrentSettings();
        if (App::runningSolelyInConsole() && !is_null($runningCommand = Artisan::lastRunningCommand())) {
            $this->setForcedInternalSettings($runningCommand->settings());
        }
    }
}
