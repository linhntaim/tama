<?php

namespace App\Support\Console;

use App\Support\Console\Application as Artisan;
use App\Support\Console\Commands\Command;
use App\Support\Console\Scheduling\Scheduler;
use Illuminate\Console\Scheduling\Schedule as ConsoleSchedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Throwable;

/**
 * @property Artisan $artisan
 */
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param ConsoleSchedule $schedule
     * @return void
     */
    protected function schedule(ConsoleSchedule $schedule): void
    {
        (new Scheduler())($this->getArtisan(), $schedule);
    }

    protected function getArtisan(): Artisan
    {
        if (is_null($this->artisan)) {
            $this->artisan = (new Artisan($this->app, $this->events, $this->app->version()))
                ->resolveCommands($this->commands)
                ->setContainerCommandLoader();
        }

        return $this->artisan;
    }

    public function call($command, array $parameters = [], $outputBuffer = null): int
    {
        if (empty($parameters) && !is_subclass_of($command, SymfonyCommand::class) && ($pos = mb_strpos($command, ' ')) !== false) {
            if ($this->commandsLoaded) {
                $settingsParameters = [];
                foreach ($this->lastRunningCommand()?->settingsParameters() as $name => $value) {
                    $settingsParameters[] = sprintf('%s=%s', $name, $value);
                }
                $command = mb_substr($command, 0, $pos) . ' ' . implode(' ', $settingsParameters) . mb_substr($command, $pos);
            }
            $command .= ' ' . Command::PARAMETER_OFF_SHOUT_OUT;
        }
        else {
            $parameters[Command::PARAMETER_OFF_SHOUT_OUT] = true;
            if ($this->commandsLoaded) {
                $parameters = array_merge($this->lastRunningCommand()?->settingsParameters(), $parameters);
            }
        }
        return parent::call($command, $parameters, $outputBuffer);
    }

    public function rootRunningCommand(): ?RunningCommand
    {
        return $this->getArtisan()->rootRunningCommand();
    }

    public function lastRunningCommand(): ?RunningCommand
    {
        return $this->getArtisan()->lastRunningCommand();
    }

    public function renderThrowable(Throwable $e, $output): void
    {
        $this->getArtisan()->renderThrowable($e, $output);
    }

    public function findCommand(string $name): SymfonyCommand
    {
        return $this->getArtisan()->find($name);
    }
}
