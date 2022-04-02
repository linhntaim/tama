<?php

/**
 * Base
 */

namespace App\Support\Console;

use App\Support\Console\Commands\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;

class RunningCommand
{
    public ?SymfonyCommand $command = null;

    public ?InputInterface $input = null;

    protected ?array $settingsParameters = null;

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

    public function settingsParameters(): array
    {
        if (is_null($this->settingsParameters)) {
            if ($this->command instanceof Command) {
                $this->settingsParameters = $this->command->settingsArguments();
            }
            else {
                $this->settingsParameters = [];
                $this->setSettingsParameter('--x-client');
                foreach (array_keys(config_starter('client.settings.default')) as $name) {
                    $this->setSettingsParameter("--x-$name");
                }
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