<?php

namespace App\Support\Console;

use App\Support\Client\Settings;
use App\Support\Console\Commands\Command;
use App\Support\Console\Concerns\ExecutionWrap;
use Illuminate\Console\Application as BaseApplication;
use Illuminate\Console\BufferedConsoleOutput;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Application extends BaseApplication
{
    use ExecutionWrap;

    /**
     * @var RunningCommand[]
     */
    protected array $runningCommands = [];

    public function findByNamespaces(string|array $namespaces): array
    {
        $namespaces = array_map(static fn($namespace) => $namespace . ':', (array)$namespaces);

        $found = [];
        foreach ($this->all() as $command) {
            if ($command->isHidden()) {
                continue;
            }

            if (Str::startsWith($command->getName(), $namespaces)) {
                $found[] = $command;
                continue;
            }

            foreach ($command->getAliases() as $alias) {
                if (Str::startsWith($alias, $namespaces)) {
                    $found[] = $command;
                    break;
                }
            }
        }
        return $found;
    }

    protected function addToParent(SymfonyCommand $command): SymfonyCommand
    {
        // debug
        $command->addOption(Command::OPTION_DEBUG, null, InputOption::VALUE_NONE);
        // shout-out
        $command->addOption(Command::OPTION_OFF_SHOUT_OUT, null, InputOption::VALUE_NONE);
        // client
        $command->addOption(Command::OPTION_CLIENT, null, InputOption::VALUE_REQUIRED);
        foreach (Settings::names() as $name) {
            $command->addOption("x-$name", null, InputOption::VALUE_REQUIRED);
        }
        return parent::addToParent($command);
    }

    public function call($command, array $parameters = [], $outputBuffer = null): int
    {
        return parent::call($command, $parameters, $outputBuffer ?: $this->lastOutput);
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        $input = $input ?: new ArgvInput;
        $output = $output ?: new BufferedConsoleOutput;
        if (!$output->getFormatter()->hasStyle('caution')) {
            $style = new OutputFormatterStyle('red');
            $output->getFormatter()->setStyle('caution', $style);
        }
        if (!$output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $output->getFormatter()->setStyle('warning', $style);
        }
        if (!$output->getFormatter()->hasStyle('error-badge')) {
            $style = new OutputFormatterStyle('white', 'red');
            $output->getFormatter()->setStyle('error-badge', $style);
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
            function (SymfonyCommand $command, InputInterface $input, OutputInterface $output) {
                return parent::doRunCommand($command, $input, $output);
            }
        );
    }

    public function startRunningCommand(SymfonyCommand $command, InputInterface $input): void
    {
        $this->runningCommands[] = (new RunningCommand())
            ->setCommand($command)
            ->setInput($input);
    }

    public function endRunningCommand(): void
    {
        array_pop($this->runningCommands);
    }

    public function rootRunningCommand(): ?RunningCommand
    {
        return $this->runningCommands[0] ?? null;
    }

    public function lastRunningCommand(): ?RunningCommand
    {
        return $this->runningCommands[count($this->runningCommands) - 1] ?? null;
    }

    public function renderThrowable(Throwable $e, OutputInterface $output): void
    {
        $runningCommand = $this->rootRunningCommand();
        $this->wrapException($runningCommand?->command, $runningCommand?->input, $output, $e);
    }
}
