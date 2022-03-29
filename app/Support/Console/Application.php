<?php

namespace App\Support\Console;

use App\Support\Console\Commands\Command;
use Illuminate\Console\Application as BaseApplication;
use Illuminate\Console\BufferedConsoleOutput;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Application extends BaseApplication
{
    /**
     * @var SymfonyCommand[]
     */
    protected array $runningCommands = [];

    protected function addToParent(SymfonyCommand $command): SymfonyCommand
    {
        return parent::addToParent(
            $command->addOption(Command::OPTION_OFF_SHOUT_OUT, null, InputOption::VALUE_NONE)
        );
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        $output = $output ?: new BufferedConsoleOutput;
        if ($output instanceof OutputInterface) {
            if (!$output->getFormatter()->hasStyle('caution')) {
                $style = new OutputFormatterStyle('red');
                $output->getFormatter()->setStyle('caution', $style);
            }
            if (!$output->getFormatter()->hasStyle('strong-caution')) {
                $style = new OutputFormatterStyle('black', 'red');
                $output->getFormatter()->setStyle('strong-caution', $style);
            }
        }
        return parent::run($input, $output);
    }

    /**
     * @throws Throwable
     */
    protected function doRunCommand(SymfonyCommand $command, InputInterface $input, OutputInterface $output): int
    {
        $this->startRunningCommand($command);
        $exitCode = parent::doRunCommand($command, $input, $output);
        $this->endRunningCommand();
        return $exitCode;
    }

    public function startRunningCommand(SymfonyCommand $command)
    {
        $this->runningCommands[] = $command;
    }

    public function endRunningCommand()
    {
        array_pop($this->runningCommands);
    }

    public function rootRunningCommand(): ?SymfonyCommand
    {
        return $this->runningCommands[0] ?? null;
    }

    public function currentRunningCommand(): ?SymfonyCommand
    {
        return $this->runningCommands[count($this->runningCommands) - 1] ?? null;
    }

    public function renderThrowable(Throwable $e, OutputInterface $output): void
    {
        $output->writeln('', OutputInterface::VERBOSITY_QUIET);
        do {
            $output->writeln(sprintf('<strong-caution>%s</strong-caution>', get_debug_type($e)), OutputInterface::VERBOSITY_QUIET);
            $output->writeln(sprintf('%s', $e->getMessage()), OutputInterface::VERBOSITY_QUIET);
            $output->writeln(sprintf('[%s:%d]', $e->getFile(), $e->getLine()), OutputInterface::VERBOSITY_QUIET);
            $output->writeln('<comment>Exception trace:</comment>', OutputInterface::VERBOSITY_QUIET);

            $traces = $e->getTrace();
            $padLength = strlen(count($traces));
            foreach ($traces as $i => $trace) {
                if (isset($trace['file'])) {
                    $output->writeln(
                        sprintf(
                            '<comment>#%s</comment> [%s:%s]',
                            str($i + 1)->padLeft($padLength, '0'),
                            $trace['file'] ?? '',
                            $trace['line'] ?? ''
                        ),
                        OutputInterface::VERBOSITY_QUIET
                    );
                    if (isset($trace['function'])) {
                        $output->writeln(
                            sprintf(
                                '%s %s%s%s(%s)',
                                str_repeat(' ', $padLength + 1),
                                $trace['class'] ?? '',
                                $trace['type'] ?? '',
                                $trace['function'] ?? '',
                                implode(', ', array_map(fn($arg) => sprintf('<comment>%s</comment>', describe_var($arg)), $trace['args'] ?? []))
                            ),
                            OutputInterface::VERBOSITY_QUIET
                        );
                    }
                }
                else {
                    if (isset($trace['function'])) {
                        $output->writeln(
                            sprintf(
                                '<comment>#%s</comment> %s%s%s(%s)',
                                str($i + 1)->padLeft($padLength, '0'),
                                $trace['class'] ?? '',
                                $trace['type'] ?? '',
                                $trace['function'] ?? '',
                                implode(', ', array_map(fn($arg) => sprintf('<comment>%s</comment>', describe_var($arg)), $trace['args'] ?? []))
                            ),
                            OutputInterface::VERBOSITY_QUIET
                        );
                    }
                    else {
                        $output->writeln(
                            sprintf(
                                '<comment>#%s</comment> %s',
                                str($i + 1)->padLeft($padLength, '0'),
                                json_encode($trace)
                            ),
                            OutputInterface::VERBOSITY_QUIET
                        );
                    }
                }
            }
        }
        while ($e = $e->getPrevious());
        $output->writeln('', OutputInterface::VERBOSITY_QUIET);
        if ($command = $this->rootRunningCommand()) {
            $output->writeln(sprintf('<caution>Command [%s] failed.</caution>', $command->getName()), OutputInterface::VERBOSITY_QUIET);
            Log::info(sprintf('Command [%s] failed.', $command::class));
        }
    }
}