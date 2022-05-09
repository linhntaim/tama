<?php

namespace App\Console\Commands\Setup;

use App\Support\Console\Commands\ForceCommand;
use App\Support\EnvironmentFile;

class KeyGenerateCommand extends ForceCommand
{
    protected function handling(): int
    {
        $environmentFile = new EnvironmentFile($this->laravel->environmentFilePath());
        if (!$environmentFile->exists()) {
            return $this->exitFailure();
        }
        if ($environmentFile->filled('APP_KEY') && !$this->forced()) {
            $this->error('Application key already set.');
            return $this->exitSuccess();
        }
        return $this->call('key:generate');
    }
}
