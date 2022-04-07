<?php

namespace App\Support\Exceptions;

use App\Support\Console\Sheller;

class ShellException extends Exception
{
    protected ?string $output;

    public function __construct(Sheller $sheller)
    {
        parent::__construct(sprintf('Shell [%s] failed.', $sheller->cmd()), $sheller->exitCode());

        $this->output = $sheller->output();
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }
}