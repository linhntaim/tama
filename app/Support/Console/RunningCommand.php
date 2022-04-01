<?php

/**
 * Base
 */

namespace App\Support\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class RunningCommand
{
    public ?Command $command = null;

    public ?InputInterface $input = null;

    protected ?array $settingsParameters = null;

    public function setCommand(Command $command): static
    {
        $this->command = $command;
        return $this;
    }

    public function setInput(InputInterface $input): static
    {
        $this->input = $input;
        return $this;
    }

    public function settingsParameters(): array
    {
        if (is_null($this->settingsParameters)) {
            $this->settingsParameters = [];
            $this->setSettingsParameter('--x-client');
            foreach (array_keys(config_starter('client.settings.default')) as $name) {
                $this->setSettingsParameter("--x-$name");
            }
        }
        return $this->settingsParameters;
    }

    protected function setSettingsParameter($name)
    {
        if (!is_null($value = $this->input->getParameterOption($name, null))) {
            $this->settingsParameters[$name] = $value;
        }
    }
}