<?php

namespace App\Support\Jobs;

use App\Support\Client\Concerns\InternalSettings;
use App\Support\Concerns\ClassHelper;
use App\Support\Facades\App;
use App\Support\Facades\Artisan;
use App\Support\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class Job
{
    use ClassHelper, Dispatchable, InternalSettings;

    public function __construct()
    {
        $this->captureCurrentSettings();
        if (App::runningSolelyInConsole() && !is_null($runningCommand = Artisan::lastRunningCommand())) {
            $this->setForcedInternalSettings($runningCommand->settings());
        }
    }

    protected function handleBefore(): void
    {
    }

    protected function handleAfter(): void
    {
    }

    abstract protected function handling(): void;

    final public function handle(): void
    {
        if (App::runningSolelyInConsole() && is_null($runningCommand = Artisan::lastRunningCommand())) {
            $this->setForcedInternalSettings($runningCommand->settings());
        }
        $this->withInternalSettings(function () {
            Log::info(sprintf('Job [%s] started.', $this->className()));
            $this->handleBefore();
            $this->handling();
            $this->handleAfter();
            Log::info(sprintf('Job [%s] ended.', $this->className()));
        });
    }

    public function failed(?Throwable $e = null): void
    {
    }
}
