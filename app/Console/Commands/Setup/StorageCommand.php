<?php

namespace App\Console\Commands\Setup;

use App\Support\Console\Commands\ForceCommand;
use App\Support\Exceptions\FileException;
use App\Support\Exceptions\ShellException;

class StorageCommand extends ForceCommand
{
    protected function links(): array
    {
        return $this->laravel['config']['filesystems.links'] ??
            [public_path('storage') => storage_path('app/public')];
    }

    protected function handling(): int
    {
        foreach ([
            's3',
            'azure',
            'ftp',
            'sftp',
            'storageLink',
        ] as $method) {
            if (!$this->{$method}()) {
                return $this->exitFailure();
            }
        }
        return $this->exitSuccess();
    }

    /**
     * @throws ShellException
     */
    protected function s3(): bool
    {
        if (config_starter('filesystems.uses.s3')) {
            return $this->handleShell('composer require -W league/flysystem-aws-s3-v3 "^3.0"') === self::SUCCESS;
        }
        return true;
    }

    /**
     * @throws ShellException
     */
    protected function azure(): bool
    {
        if (config_starter('filesystems.uses.azure')) {
            return $this->handleShell('composer require matthewbdaly/laravel-azure-storage') === self::SUCCESS;
        }
        return true;
    }

    /**
     * @throws ShellException
     */
    protected function ftp(): bool
    {
        if (config_starter('filesystems.uses.ftp')) {
            return $this->handleShell('composer require league/flysystem-ftp "^3.0"') === self::SUCCESS;
        }
        return true;
    }

    /**
     * @throws ShellException
     */
    protected function sftp(): bool
    {
        if (config_starter('filesystems.uses.sftp')) {
            return $this->handleShell('composer require league/flysystem-sftp-v3 "^3.0"') === self::SUCCESS;
        }
        return true;
    }

    /**
     * @throws FileException
     */
    protected function storageLink(): bool
    {
        if ($this->call('storage:link', [
                '--force' => $this->forced(),
            ]) === self::SUCCESS) {
            foreach ($this->links() as $link => $target) {
                if (!file_exists($link)) {
                    mkdir_for_writing($link);
                    copy_recursive($target, $link);
                }
            }
            return true;
        }
        return false;
    }
}
