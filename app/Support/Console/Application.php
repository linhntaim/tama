<?php

namespace App\Support\Console;

use App\Support\Console\Commands\Command;
use Illuminate\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputOption;

class Application extends BaseApplication
{
    protected function addToParent(SymfonyCommand $command)
    {
        return parent::addToParent(
            $command->addOption(Command::OPTION_OFF_ANNOUNCEMENT, null, InputOption::VALUE_NONE)
        );
    }
}