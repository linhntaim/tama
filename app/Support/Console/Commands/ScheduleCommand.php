<?php

namespace App\Support\Console\Commands;

use App\Support\Console\Schedules\Schedule;

abstract class ScheduleCommand extends Command
{
    protected string $scheduleClass;

    protected array $scheduleParams = [];

    protected function getScheduleClass(): string
    {
        return $this->scheduleClass;
    }

    protected function getScheduleParams(): array
    {
        return $this->scheduleParams;
    }

    protected function getSchedule(): ?Schedule
    {
        if (is_subclass_of($class = $this->getScheduleClass(), Schedule::class)) {
            return new $class(...$this->getScheduleParams());
        }
        return null;
    }

    protected function handling(): int
    {
        $this->runSchedule();
        return $this->exitSuccess();
    }

    protected function runSchedule()
    {
        $this->getSchedule()?->__invoke();
    }
}