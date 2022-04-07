<?php

namespace App\Support\Console;

use App\Support\App;
use App\Support\Console\Schedules\Schedule;
use App\Support\Jobs\Job;
use Illuminate\Console\Scheduling\Event as ConsoleScheduleEvent;
use Illuminate\Console\Scheduling\Schedule as ConsoleSchedule;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class Scheduler
{
    protected ConsoleSchedule $schedule;

    protected array $commandNames;

    protected ?array $forcedInternalSettings = null;

    protected ?string $name = null;

    protected array $params = [];

    protected ?string $description = null;

    protected function reset(): static
    {
        $this->name = null;
        $this->params = [];
        $this->description = null;
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

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    protected function parse($scheduleCaller, array $scheduleCallerParams = [], array $methodParams = []): static
    {
        if (is_array($scheduleCaller)) {
            return $this->parse(
                $scheduleCaller[0] ?? null,
                (array)($scheduleCaller[1] ?? []),
                (array)($scheduleCaller[2] ?? [])
            );
        }

        if (is_string($scheduleCaller)) {
            $composeCommandParams = function (array $commandParams): array {
                foreach (($this->forcedInternalSettings ?? []) as $name => $value) {
                    $commandParams["--x-$name"] = $value;
                }
                return $commandParams;
            };
            $composeCommandParamsDescription = function (array $commandParams): string {
                $commandArgs = [];
                foreach ($commandParams as $name => $value) {
                    $commandArgs[] = str($name)->startsWith('--')
                        ? sprintf('%s="%s"', $name, str_replace('"', '\\"', $value))
                        : sprintf('"%s"', str_replace('"', '\\"', $value));
                }
                return implode(' ', $commandArgs);
            };
            if (is_subclass_of($scheduleCaller, Schedule::class)) {
                $this
                    ->setName('call')
                    ->addParams(new $scheduleCaller(...$scheduleCallerParams))
                    ->setDescription($scheduleCaller);
            }
            elseif (is_subclass_of($scheduleCaller, Job::class)) {
                $this
                    ->setName('job')
                    ->addParams(new $scheduleCaller(...$scheduleCallerParams), ...$methodParams);
            }
            elseif (is_subclass_of($scheduleCaller, SymfonyCommand::class)) {
                $commandParamsDescription = $composeCommandParamsDescription(
                    $commandParams = $composeCommandParams($scheduleCallerParams)
                );
                $this
                    ->setName('command')
                    ->addParams($scheduleCaller, $commandParams)
                    ->setDescription(
                        sprintf(
                            '%s%s > "NUL" 2>&1',
                            $scheduleCaller,
                            $commandParamsDescription ? ' ' . $commandParamsDescription : ''
                        )
                    );
            }
            else {
                $commandName = strstr($scheduleCaller, ' ', true) ?: $scheduleCaller;
                if (in_array($commandName, $this->commandNames)) {
                    $commandParamsDescription = $composeCommandParamsDescription(
                        $commandParams = $composeCommandParams($commandName == $scheduleCaller ? $scheduleCallerParams : [])
                    );
                    $this
                        ->setName('command')
                        ->addParams($scheduleCaller, $commandParams)
                        ->setDescription(
                            sprintf(
                                '"%s" "artisan" %s%s > "NUL" 2>&1',
                                PHP_BINARY,
                                $scheduleCaller,
                                $commandParamsDescription ? ' ' . $commandParamsDescription : ''
                            )
                        );
                }
                else {
                    $this
                        ->setName('exec')
                        ->addParams($scheduleCaller)
                        ->setDescription(sprintf('%s > "NUL" 2>&1', $scheduleCaller));
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
            return $this->description ? $scheduleEvent->description($this->description) : $scheduleEvent;
        }
        return null;
    }

    public function __invoke(Application $application, ConsoleSchedule $schedule): static
    {
        $this->schedule = $schedule;
        $this->commandNames = array_keys($application->all());
        if (App::runningSolelyInConsole()) {
            if ($runningCommand = $application->lastRunningCommand()) {
                $this->forcedInternalSettings = $runningCommand->settings();
            }
        }

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