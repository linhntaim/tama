<?php

namespace App\Console;

use App\Support\Commands\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    public function call($command, array $parameters = [], $outputBuffer = null): int
    {
        $parameters[Command::PARAMETER_OFF_ANNOUNCEMENT] = true;
        return parent::call($command, $parameters, $outputBuffer);
    }
}
