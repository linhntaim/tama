<?php

/**
 * Base
 */

namespace App\Console;

use App\Support\Console\Application as Artisan;
use App\Support\Console\Commands\Command;
use App\Support\Console\RunningCommand;
use App\Support\Console\Scheduler;
use Illuminate\Console\Scheduling\Schedule as ConsoleSchedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Symfony\Component\Console\Output\OutputInterface;
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
    protected function schedule(ConsoleSchedule $schedule)
    {
        (new Scheduler())($this->getArtisan(), $schedule);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
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
        $parameters[Command::PARAMETER_OFF_SHOUT_OUT] = true;
        if ($this->commandsLoaded) {
            $parameters = array_merge($this->lastRunningCommand()->settingsParameters(), $parameters);
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

    public function renderThrowable(Throwable $e, $output)
    {
        $this->getArtisan()->renderThrowable($e, $output);
    }
}
