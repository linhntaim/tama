<?php

namespace App\Console\Commands\MailHog;

use App\Support\Console\Commands\Command as BaseCommand;

abstract class Command extends BaseCommand
{
    protected string $binFile;

    protected function binFilename(): string
    {
        return match (PHP_OS_FAMILY) {
            'Windows' => 'mailhog.exe',
            default => 'mailhog',
        };
    }

    protected function binFile(): string
    {
        return storage_path(
            concat_paths(
                true,
                'framework',
                'bin',
                'mailhog',
                $this->binFilename()
            )
        );
    }

    protected function handleBefore(): void
    {
        $this->binFile = $this->binFile();
        parent::handleBefore();
    }
}
