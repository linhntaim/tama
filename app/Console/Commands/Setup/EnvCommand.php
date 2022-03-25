<?php

/**
 * Base
 */

namespace App\Console\Commands\Setup;

use App\Support\Console\Commands\ForceCommand;

class EnvCommand extends ForceCommand
{
    protected function handling(): int
    {
        if (file_exists($this->laravel->environmentFilePath()) && !$this->forced()) {
            $this->error('The [.env] file already exists!');
        }
        if (!$this->createEnvFile()) {
            return $this->exitFailure();
        }
        $this->info('The [.env] file was created!');
        return $this->exitSuccess();
    }

    protected function createEnvFile(): bool
    {
        return copy($this->laravel->basePath('.env.example'), $this->laravel->environmentFilePath()) === true;
    }
}