<?php

namespace App\Support\Console\Schedules;

use App\Support\ClassTrait;
use App\Support\Client\InternalSettings;
use App\Support\Facades\Artisan;
use App\Support\Facades\App;
use Illuminate\Support\Facades\Log;

abstract class Schedule
{
    use ClassTrait, InternalSettings;

    public function __construct()
    {
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

    final public function __invoke(): static
    {
        return $this->withInternalSettings(function () {
            Log::info(sprintf('Schedule [%s] started.', $this->className()));
            $this->handleBefore();
            $this->handling();
            $this->handleAfter();
            Log::info(sprintf('Schedule [%s] ended.', $this->className()));
            return $this;
        });
    }
}