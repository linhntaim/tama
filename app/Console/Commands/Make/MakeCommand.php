<?php

namespace App\Console\Commands\Make;

use App\Support\Commands\Command;

abstract class MakeCommand extends Command
{
    protected function forced(): bool
    {
        return $this->option('force');
    }

    protected function handling(): int
    {
        return $this->making();
    }

    protected abstract function making(): int;
}