<?php

namespace App\Support\Jobs;

use App\Support\ClassTrait;
use App\Support\Client\InternalSettings;
use App\Support\Facades\App;
use App\Support\Facades\Artisan;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class Job
{
    use ClassTrait, Dispatchable, InternalSettings;

    public function __construct()
    {
        $this->captureCurrentSettings();
        if (App::runningSolelyInConsole()) {
            if ($runningCommand = Artisan::lastRunningCommand()) {
                $this->setForcedInternalSettings($runningCommand->settings());
            }
        }
    }

    protected function handleBefore()
    {
    }

    protected function handleAfter()
    {
    }

    protected abstract function handling();

    final public function handle()
    {
        if (App::runningSolelyInConsole()) {
            if ($runningCommand = Artisan::lastRunningCommand()) {
                $this->setForcedInternalSettings($runningCommand->settings());
            }
        }
        $this->withInternalSettings(function () {
            Log::info(sprintf('Job [%s] started.', $this->className()));
            $this->handleBefore();
            $this->handling();
            $this->handleAfter();
            Log::info(sprintf('Job [%s] ended.', $this->className()));
        });
    }

    public function failed(?Throwable $e = null)
    {
    }
}