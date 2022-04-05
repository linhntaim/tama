<?php

/**
 * Base
 */

namespace App\Support\Console;

use App\Support\Client\Client;
use App\Support\Client\ParseSettingsTrait;
use App\Support\Console\Commands\Command;
use App\Support\Console\Commands\WrapCommandTrait;
use Illuminate\Console\Application as BaseApplication;
use Illuminate\Console\BufferedConsoleOutput;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Application extends BaseApplication
{
    use WrapCommandTrait, ParseSettingsTrait;

    /**
     * @var RunningCommand[]
     */
    protected array $runningCommands = [];

    protected function addToParent(SymfonyCommand $command): SymfonyCommand
    {
        // shout-out
        $command->addOption(Command::OPTION_OFF_SHOUT_OUT, null, InputOption::VALUE_NONE);
        // client
        $command->addOption('x-client', null, InputOption::VALUE_REQUIRED);
        foreach (array_keys(config_starter('client.settings.default')) as $name) {
            $command->addOption("x-$name", null, InputOption::VALUE_REQUIRED);
        }
        return parent::addToParent($command);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        $input = $input ?: new ArgvInput;
        $output = $output ?: new BufferedConsoleOutput;
        if (!$output->getFormatter()->hasStyle('caution')) {
            $style = new OutputFormatterStyle('red');
            $output->getFormatter()->setStyle('caution', $style);
        }
        if (!$output->getFormatter()->hasStyle('strong-caution')) {
            $style = new OutputFormatterStyle('black', 'red');
            $output->getFormatter()->setStyle('strong-caution', $style);
        }

        $settings = [];
        if ($client = $input->getParameterOption('x-client', null)) {
            $settings = $this->parseSettings($client);
        }
        foreach ($this->settingsNames() as $name) {
            if ($value = $input->getParameterOption("x-$name", null)) {
                $settings[$name] = $value;
            }
        }
        if (count($settings)) {
            return Client::settingsTemporary($settings, function () use ($input, $output) {
                return parent::run($input, $output);
            });
        }
        return parent::run($input, $output);
    }

    /**
     * @throws Throwable
     */
    protected function doRunCommand(SymfonyCommand $command, InputInterface $input, OutputInterface $output): int
    {
        return $this->wrapRunning(
            $this->laravel,
            $this,
            $command,
            $input,
            $output,
            function ($command, $input, $output) {
                return parent::doRunCommand($command, $input, $output);
            }
        );
    }

    public function startRunningCommand(SymfonyCommand $command, InputInterface $input)
    {
        $this->runningCommands[] = (new RunningCommand())
            ->setCommand($command)
            ->setInput($input);
    }

    public function endRunningCommand()
    {
        array_pop($this->runningCommands);
    }

    public function rootRunningCommand(): ?RunningCommand
    {
        return $this->runningCommands[0] ?? null;
    }

    public function latestRunningCommand(): ?RunningCommand
    {
        return $this->runningCommands[count($this->runningCommands) - 1] ?? null;
    }

    public function renderThrowable(Throwable $e, OutputInterface $output): void
    {
        $runningCommand = $this->rootRunningCommand();
        $this->wrapException($runningCommand?->command, $runningCommand?->input, $output, $e);
    }
}