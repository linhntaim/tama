<?php

/**
 * Base
 */

namespace App\Support\Console\Commands;

use Symfony\Component\Console\Input\InputOption;

abstract class ForceCommand extends Command
{
    protected function forced(): bool
    {
        return !$this->hasOption('force') || $this->option('force');
    }

    protected function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            ['force', null, InputOption::VALUE_NONE],
        ]);
    }
}