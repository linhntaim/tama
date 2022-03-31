<?php

/**
 * Base
 */

namespace App\Support\Console\Commands;

use Symfony\Component\Console\Input\InputOption;

abstract class ForceCommand extends Command
{
    protected ?bool $forced = null;

    protected function forced(): bool
    {
        return is_null($this->forced)
            ? ($this->forced = !$this->hasOption('force') || $this->option('force'))
            : $this->forced;
    }

    protected function handleBefore(): void
    {
        parent::handleBefore();

        $this->forced() && $this->whenForced();
    }

    protected function whenForced()
    {
    }

    protected function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            ['force', null, InputOption::VALUE_NONE],
        ]);
    }
}