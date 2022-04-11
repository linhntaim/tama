<?php

namespace App\Console\Commands\Make\Command;

use App\Support\Console\Commands\GeneratorCommand;

class TryCommand extends GeneratorCommand
{
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected string $type = 'Command';

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/command.try.stub');
    }

    protected function getNameInput(): string
    {
        return 'TryCommand';
    }

    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace . '\Console\Commands';
    }

    protected function getDefaultArguments(): array
    {
        $arguments = parent::getDefaultArguments();
        array_pop($arguments);
        return $arguments;
    }
}