<?php

namespace App\Support\Console\Commands;

use App\Support\Exceptions\ShellException;

abstract class ShellCommand extends Command
{
    protected abstract function getShell(): string;

    /**
     * @throws ShellException
     */
    protected function handling(): int
    {
        return $this->handleShell($this->getShell());
    }
}