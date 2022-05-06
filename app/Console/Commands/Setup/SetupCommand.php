<?php

namespace App\Console\Commands\Setup;

use App\Support\Console\Commands\ForceCommand;

class SetupCommand extends ForceCommand
{
    protected function handling(): int
    {
        if (!file_exists($this->laravel->environmentFilePath())) {
            return $this->call('setup:env', [
                '--force' => $this->forced(),
            ]);
        }

        foreach ([
                     'webServer',
                     'keyGenerate',
                     'storage',
                     'migrate',
                 ] as $i => $method) {
            $i > 0 && $this->newLine();
            if (!$this->{$method}()) {
                return $this->exitFailure();
            }
        }
        return $this->exitSuccess();
    }

    protected function keyGenerate(): bool
    {
        return $this->call('setup:key-generate', [
                '--force' => $this->forced(),
            ]) == self::SUCCESS;
    }

    protected function webServer(): bool
    {
        return $this->call('setup:web-server', [
                '--force' => $this->forced(),
            ]) == self::SUCCESS;
    }

    protected function storage(): bool
    {
        return $this->call('setup:storage', [
                '--force' => $this->forced(),
            ]) == self::SUCCESS;
    }

    protected function migrate(): bool
    {
        return $this->call('setup:migrate', [
                '--force' => $this->forced(),
            ]) == self::SUCCESS;
    }
}