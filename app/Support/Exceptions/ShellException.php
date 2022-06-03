<?php

namespace App\Support\Exceptions;

use App\Support\Console\Sheller;
use BadMethodCallException;
use Throwable;

class ShellException extends Exception
{
    public static function from(Throwable $throwable, array|string|null $message = null): static
    {
        throw new BadMethodCallException('Method not supported.');
    }

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
