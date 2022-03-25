<?php

/**
 * Base
 */

namespace App\Console\Commands\Make\Command;

use App\Console\Commands\Make\MakeCommand;

class TryCommand extends MakeCommand
{
    protected $signature = 'make:command:try {--force}';

    protected string $tryFilePath;

    protected function remove()
    {
        unlink($this->tryFilePath);
    }

    protected function has(): bool
    {
        return file_exists($this->tryFilePath);
    }

    protected function copy()
    {
        copy($this->exampleTryFilePath(), $this->tryFilePath);
    }

    protected function tryFilePath(): string
    {
        return app_path(implode(DIRECTORY_SEPARATOR, ['Console', 'Commands', 'TryCommand.php']));
    }

    protected function exampleTryFilePath(): string
    {
        return app_path(implode(DIRECTORY_SEPARATOR, ['Console', 'Commands', 'TryCommand.php.example']));
    }

    protected function making(): int
    {
        $this->tryFilePath = $this->tryFilePath();
        if ($this->has()) {
            if ($this->forced()) {
                $this->remove();
                $this->copy();
            }
        }
        else {
            $this->copy();
        }
        return $this->exitSuccess();
    }
}