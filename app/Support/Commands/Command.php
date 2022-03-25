<?php

/**
 * Base
 */

namespace App\Support\Commands;

use App\Support\ClassTrait;
use Illuminate\Console\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

abstract class Command extends BaseCommand
{
    use ClassTrait;

    public const OPTION_OFF_ANNOUNCEMENT = 'off-announcement';
    public const PARAMETER_OFF_ANNOUNCEMENT = '--' . self::OPTION_OFF_ANNOUNCEMENT;

    public function __construct()
    {
        if (isset($this->signature)) {
            if (trim($this->signature)[0] == '{') {
                $this->signature = $this->generateName() . ' ' . $this->signature;
            }
        }
        else {
            if (!isset($this->name)) {
                $this->name = $this->generateName();
            }
        }

        parent::__construct();
    }

    protected function generateName(): string
    {
        return implode(':', array_map(function ($name) {
            return str($name)->snake('-')->toString();
        }, explode('\\', preg_replace('/^App\\\\Console\\\\Commands\\\\|Command$/', '', $this->className()))));
    }

    protected function configure()
    {
        parent::configure();
        $this->specifyDefaultParameters();
    }

    public function call($command, array $arguments = []): int
    {
        $arguments[self::PARAMETER_OFF_ANNOUNCEMENT] = true;
        return parent::call($command, $arguments);
    }

    public function callSilent($command, array $arguments = [])
    {
        $arguments[self::PARAMETER_OFF_ANNOUNCEMENT] = true;
        return parent::callSilent($command, $arguments);
    }

    protected function specifyDefaultParameters()
    {
        foreach ($this->getDefaultArguments() as $arguments) {
            if ($arguments instanceof InputArgument) {
                $this->getDefinition()->addArgument($arguments);
            }
            else {
                $this->addArgument(...array_values($arguments));
            }
        }

        foreach ($this->getDefaultOptions() as $options) {
            if ($options instanceof InputOption) {
                $this->getDefinition()->addOption($options);
            }
            else {
                $this->addOption(...array_values($options));
            }
        }
    }

    protected function getDefaultArguments(): array
    {
        return [];
    }

    protected function getDefaultOptions(): array
    {
        return [
            [self::OPTION_OFF_ANNOUNCEMENT, null, InputOption::VALUE_NONE, ''],
        ];
    }

    protected function offAnnouncement(): bool
    {
        return $this->option(self::OPTION_OFF_ANNOUNCEMENT);
    }

    protected function handleBefore(): void
    {
        if (!$this->offAnnouncement()) {
            $this->info(sprintf('Console start [%s]', $this->getName()));
            $this->newLine();
        }
    }

    protected function handleAfter(): void
    {
        if (!$this->offAnnouncement()) {
            $this->newLine();
            $this->info(sprintf('Console end [%s]', $this->getName()));
        }
    }

    public function handle(): int
    {
        $this->handleBefore();
        $exit = $this->handling();
        $this->handleAfter();
        return $exit;
    }

    protected abstract function handling(): int;

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