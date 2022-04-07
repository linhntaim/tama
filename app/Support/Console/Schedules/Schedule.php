<?php

namespace App\Support\Console\Schedules;

use App\Support\App;
use App\Support\ClassTrait;
use App\Support\Client\InternalSettingsTrait;
use App\Support\Console\Artisan;
use Illuminate\Support\Facades\Log;

abstract class Schedule
{
    use ClassTrait, InternalSettingsTrait;

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

    public function __invoke(): static
    {
        Log::info(sprintf('Schedule [%s] started.', $this->className()));
        $this->handleBefore();
        $this->withInternalSettings(function () {
            $this->handling();
        });
        $this->handleAfter();
        Log::info(sprintf('Schedule [%s] ended.', $this->className()));
        return $this;
    }
}