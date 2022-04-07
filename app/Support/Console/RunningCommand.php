<?php

/**
 * Base
 */

namespace App\Support\Console;

use App\Support\Client\Settings;
use App\Support\Console\Commands\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;

class RunningCommand
{
    public ?SymfonyCommand $command = null;

    public ?InputInterface $input = null;

    protected ?array $settingsParameters = null;

    protected ?array $settings = null;

    public function setCommand(SymfonyCommand $command): static
    {
        $this->command = $command;
        return $this;
    }

    public function setInput(InputInterface $input): static
    {
        $this->input = $input;
        return $this;
    }

    public function settings(): array
    {
        if (is_null($this->settings)) {
            $this->settings = [];
            if ($this->command instanceof Command) {
                $this->settings = $this->command->getFinalInternalSettings();
            }
            else {
                $this->settings = Settings::parseConfig($this->input->getParameterOption('--x-client', null));
                foreach (Settings::names() as $name) {
                    if (!is_null($value = $this->input->getParameterOption("--x-$name", null))) {
                        $this->settings[$name] = $value;
                    }
                }
            }
        }
        return $this->settings;
    }

    public function settingsParameters(): array
    {
        if (is_null($this->settingsParameters)) {
            $this->settingsParameters = [];
            if ($this->command instanceof Command) {
                foreach ($this->command->getFinalInternalSettings() as $name => $value) {
                    $this->settingsParameters["--x-$name"] = $value;
                }
            }
            else {
                if (!is_null($value = $this->input->getParameterOption('--x-client', null))) {
                    $this->settingsParameters['--x-client'] = $value;
                }
                foreach (Settings::names() as $name) {
                    if (!is_null($value = $this->input->getParameterOption("--x-$name", null))) {
                        $this->settingsParameters["--x-$name"] = $value;
                    }
                }
            }
        }
        return $this->settingsParameters;
    }
}