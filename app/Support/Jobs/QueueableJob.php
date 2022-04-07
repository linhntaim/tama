<?php

namespace App\Support\Jobs;

use App\Support\App;
use App\Support\Bus\Queueable;
use App\Support\Client\Client;
use App\Support\Client\InternalSettingsTrait;
use App\Support\Console\Artisan;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class QueueableJob extends Job implements ShouldQueue
{
    use Queueable, InternalSettingsTrait;

    public function __construct()
    {
        $this->captureCurrentSettings();
    }

    public function handle()
    {
        if (App::runningSolelyInConsole()) {
            if ($runningCommand = Artisan::lastRunningCommand()) {
                $this->setForcedInternalSettings($runningCommand->settings());
            }
        }
        $this->withInternalSettings(function () {
            parent::handle();
        });
    }
}