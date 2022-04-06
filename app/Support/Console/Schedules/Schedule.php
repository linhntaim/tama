<?php

namespace App\Support\Console\Schedules;

use App\Support\ClassTrait;
use App\Support\Client\InternalSettingsTrait;
use Illuminate\Support\Facades\Log;

abstract class Schedule
{
    use ClassTrait, InternalSettingsTrait;

    public static function run(): static
    {
        return (new static())();
    }

    protected function handleBefore()
    {
    }

    protected function handleAfter()
    {
    }

    protected abstract function handling();

    protected function handle(): static
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

    public function __invoke(): static
    {
        return $this->handle();
    }
}