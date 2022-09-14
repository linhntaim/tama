<?php

namespace App\Console;

use App\Support\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load([
            __DIR__ . '/Commands',
            //
            app_path('Trading/Console/Commands'),
        ]);

        require base_path('routes/console.php');
    }
}
