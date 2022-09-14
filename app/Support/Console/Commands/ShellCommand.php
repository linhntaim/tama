<?php

namespace App\Support\Console\Commands;

use App\Support\Exceptions\ShellException;

abstract class ShellCommand extends Command
{
    abstract protected function getShell(): string;

    /**
     * @throws ShellException
     */
    protected function handling(): int
    {
        return $this->handleShell($this->getShell());
    }
}
