<?php

/**
 * Base
 */

namespace App\Console;

use App\Support\Console\Application;
use App\Support\Console\Application as Artisan;
use App\Support\Console\Commands\Command;
use App\Support\Console\RunningCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Throwable;

/**
 * @property Application $artisan
 */
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
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

    protected function getArtisan()
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
        if ($runningCommand = $this->rootRunningCommand()) {
            $parameters = $parameters + $runningCommand->settingsParameters();
        }
        return parent::call($command, $parameters, $outputBuffer);
    }

    public function rootRunningCommand(): ?RunningCommand
    {
        return $this->getArtisan()->rootRunningCommand();
    }

    public function renderThrowable(Throwable $e, $output)
    {
        $this->getArtisan()->renderThrowable($e, $output);
    }
}
