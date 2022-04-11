<?php

namespace App\Console\Commands\Shell;

use App\Support\Console\Commands\ShellCommand;
use App\Support\Console\Sheller;

class ManualCommand extends ShellCommand
{
    protected $signature = '{shell}';

    protected function getSheller(): Sheller
    {
        return parent::getSheller()->exceptionOnError(false);
    }

    protected function getShell(): string
    {
        return $this->argument('shell');
    }
}
