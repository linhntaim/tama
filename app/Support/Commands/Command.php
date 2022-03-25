<?php

/**
 * Base
 */

namespace App\Support\Commands;

use App\Support\ClassTrait;
use Illuminate\Console\Command as BaseCommand;

abstract class Command extends BaseCommand
{
    use ClassTrait;

    protected function handleBefore(): void
    {
        $this->info(sprintf('Console start [%s]', $this->classFriendlyName()));
    }

    protected function handleAfter(): void
    {
        $this->info(sprintf('Console end [%s]', $this->classFriendlyName()));
    }

    public function handle(): int
    {
        $this->handleBefore();
        $exit = $this->handling();
        $this->handleAfter();
        return $exit;
    }

    protected function handling(): int
    {
        return $this->exitSuccess();
    }

    protected function exitSuccess(): int
    {
        return self::SUCCESS;
    }

    protected function exitFailure(): int
    {
        return self::FAILURE;
    }

    protected function exitInvalid(): int
    {
        return self::INVALID;
    }
}