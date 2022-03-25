<?php

/**
 * Base
 */

namespace App\Console\Commands;

use App\Support\Console\Commands\Command;

class AboutCommand extends Command
{
    protected function handling(): int
    {
        $this->info(sprintf('Laravel v%s', $this->laravel->version()));
        $this->info(sprintf('PHP v%s', PHP_VERSION));
        return $this->exitSuccess();
    }
}