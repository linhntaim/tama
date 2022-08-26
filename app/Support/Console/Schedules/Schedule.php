<?php

namespace App\Support\Console\Schedules;

use App\Support\Client\Concerns\InternalSettings;
use App\Support\Concerns\ClassHelper;
use App\Support\Facades\App;
use App\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

abstract class Schedule
{
    use ClassHelper, InternalSettings;

    public function __construct()
    {
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
