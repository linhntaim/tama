<?php

namespace App\Support\Console;

use App\Support\Console\Schedules\Schedule;
use App\Support\Jobs\Job;
use Illuminate\Console\Scheduling\Event as ConsoleScheduleEvent;
use Illuminate\Console\Scheduling\Schedule as ConsoleSchedule;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class Scheduler
{
    protected ConsoleSchedule $schedule;

    protected array $commandNames;

    protected ?string $name = null;

    protected array $params = [];

    protected function reset(): static
    {
        $this->name = null;
        $this->params = [];
        return $this;
    }

    protected function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    protected function addParams(...$params): static
    {
        array_push($this->params, ...$params);
        return $this;
    }

    protected function parse($scheduleCaller, $scheduleCallerParams = [], $methodParams = []): static
    {
        if (is_array($scheduleCaller)) {
            return $this->parse(
                $scheduleCaller[0] ?? null,
                $scheduleCaller[1] ?? [],
                $scheduleCaller[2] ?? []
            );
        }

        if (is_string($scheduleCaller)) {
            if (is_subclass_of($scheduleCaller, Schedule::class)) {
                $this
                    ->setName('call')
                    ->addParams(new $scheduleCaller(...$scheduleCallerParams));
            }
            elseif (is_subclass_of($scheduleCaller, Job::class)) {
                $this
                    ->setName('job')
                    ->addParams(new $scheduleCaller(...$scheduleCallerParams), ...$methodParams);
            }
            elseif (is_subclass_of($scheduleCaller, SymfonyCommand::class)) {
                $this
                    ->setName('command')
                    ->addParams($scheduleCaller, $scheduleCallerParams);
            }
            else {
                $commandName = strtok($scheduleCaller, ' ');
                if (in_array($commandName, $this->commandNames)) {
                    $this
                        ->setName('command')
                        ->addParams($scheduleCaller);
                }
                else {
                    $this
                        ->setName('exec')
                        ->addParams($scheduleCaller);
                }
            }
        }
        return $this;
    }

    protected function call(array $frequencies = []): ?ConsoleScheduleEvent
    {
        if ($this->name) {
            $scheduleEvent = $this->schedule->{$this->name}(...$this->params);
            foreach ($frequencies as $key => $value) {
                if (is_int($key)) {
                    $method = $value;
                    $parameters = [];
                }
                else {
                    $method = $key;
                    $parameters = (array)$value;
                }
                $scheduleEvent->{$method}(...$parameters);
            }
            return $scheduleEvent;
        }
        return null;
    }

    public function __invoke(Application $application, ConsoleSchedule $schedule): static
    {
        $this->schedule = $schedule;
        $this->commandNames = array_keys($application->all());

        foreach (config_starter('console.schedules.definitions') as $scheduleDefinition) {
            $scheduleCallerDefinitions = $scheduleDefinition['schedules'] ?? [];
            $frequencyDefinition = $scheduleDefinition['frequencies'] ?? [];
            if (count($scheduleCallerDefinitions) && count($frequencyDefinition)) {
                foreach ($scheduleCallerDefinitions as $scheduleCallerDefinition) {
                    $this
                        ->reset()
                        ->parse($scheduleCallerDefinition)
                        ->call($frequencyDefinition);
                }
            }
        }
        return $this;
    }
}