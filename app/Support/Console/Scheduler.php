<?php

namespace App\Support\Console;

use App\Support\App;
use App\Support\Console\Schedules\Schedule;
use App\Support\Jobs\Job;
use Closure;
use Illuminate\Console\Scheduling\Event as ConsoleScheduleEvent;
use Illuminate\Console\Scheduling\Schedule as ConsoleSchedule;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class Scheduler
{
    protected ConsoleSchedule $schedule;

    protected array $commandNames;

    protected ?string $name = null;

    protected array $params = [];

    protected ?string $debug = null;

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
            if (is_subclass_of($scheduleCaller, Schedule::class)) {
                $this
                    ->setName('call')
                    ->addParams(new $scheduleCaller(...array_values($scheduleCallerParams)));
            }
            elseif (is_subclass_of($scheduleCaller, Job::class)) {
                $this
                    ->setName('job')
                    ->addParams(new $scheduleCaller(...array_values($scheduleCallerParams)), ...$methodParams);
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

    protected function writeDebug(string|Closure $text = ''): static
    {
        if (App::runningInDebug()) {
            if (is_null($this->debug)) {
                $this->debug = value($text);
            }
            else {
                $this->debug .= value($text);
            }
        }
        return $this;
    }

    protected function shoutDebug(): static
    {
        if (App::runningSolelyInConsole()) {
            echo $this->debug;
        }
        return $this;
    }

    protected function call(array $frequencies = []): ?ConsoleScheduleEvent
    {
        if ($this->name) {
            $scheduleEvent = $this->schedule->{$this->name}(...$this->params);

            $this->writeDebug(function () {
                return sprintf(
                    '$schedule->%s(%s)',
                    $this->name,
                    implode(', ', array_map(function ($param) {
                        return describe_var($param);
                    }, $this->params))
                );
            });

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

                $this->writeDebug(function () use ($method, $parameters) {
                    return sprintf(
                        '->%s(%s)',
                        $method,
                        implode(', ', array_map(function ($param) {
                            return describe_var($param);
                        }, $parameters))
                    );
                });
            }
            $this->writeDebug(function () {
                return PHP_EOL;
            });
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
        return $this->shoutDebug();
    }
}