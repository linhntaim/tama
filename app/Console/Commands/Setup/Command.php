<?php

/**
 * Base
 */

namespace App\Console\Commands\Setup;

use App\Support\Console\Commands\ForceCommand;

class Command extends ForceCommand
{
    protected function handling(): int
    {
        if (!file_exists($this->laravel->environmentFilePath())) {
            return $this->call('setup:env', [
                '--force' => $this->forced(),
            ]);
        }
        $this->call('setup:key-generate');
        return $this->exitSuccess();
    }
}