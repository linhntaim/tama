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
        $this->output->writeln(sprintf('<comment>Laravel Framework</comment> v%s', $this->laravel->version()), $this->parseVerbosity());
        $this->output->writeln(sprintf('<comment>PHP</comment> v%s', PHP_VERSION), $this->parseVerbosity());
        $this->line(date_timer()->compound('longDate', ' - ', 'longTime'));
        return $this->exitSuccess();
    }
}