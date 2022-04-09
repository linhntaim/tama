<?php

namespace App\Support\Console\Schedules;

use App\Support\Console\Artisan;

abstract class CommandSchedule extends Schedule
{
    protected string $command;

    protected function getCommand(): string
    {
        return $this->command;
    }

    protected function getParameters(): array
    {
        return [];
    }

    protected function getParametersWithSettings(): array
    {
        $parameters = $this->getParameters();
        foreach ($this->getFinalInternalSettings() as $name => $value) {
            $parameters["--x-$name"] = $value;
        }
        return $parameters;
    }

    protected function handling()
    {
        Artisan::call($this->getCommand(), $this->getParametersWithSettings());
    }
}