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
}