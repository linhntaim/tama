<?php

/**
 * Base
 */

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
        $this->startRunningCommand($command, $input);
        $notStarterCommand = !($command instanceof Command);
        $canLog = $notStarterCommand
            && !in_array($command::class, config_starter('console.commands.logging_except'));
        if ($canLog) {
            Log::info(sprintf('Command [%s] started.', $command::class));
        }
        $canShoutOut = $notStarterCommand
            && $this->laravel->runningInConsole()
            && !$this->laravel->runningUnitTests()
            && !($arguments[Command::PARAMETER_OFF_SHOUT_OUT] ?? false);
        if ($canShoutOut) {
            $output->writeln(sprintf('<info>Command <comment>[%s]</comment> started.</info>', $command::class), OutputInterface::VERBOSITY_QUIET);
            $output->writeln('', OutputInterface::VERBOSITY_QUIET);
        }
        $exitCode = parent::doRunCommand($command, $input, $output);
        if ($canShoutOut) {
            $output->writeln('', OutputInterface::VERBOSITY_QUIET);
            $output->writeln(sprintf('<info>Command <comment>[%s]</comment> ended.</info>', $command::class), OutputInterface::VERBOSITY_QUIET);
        }
        if ($canLog) {
            Log::info(sprintf('Command [%s] ended.', $command::class));
        }
        $this->endRunningCommand();
        return $exitCode;
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

    public function currentRunningCommand(): ?RunningCommand
    {
        return $this->runningCommands[count($this->runningCommands) - 1] ?? null;
    }

    public function renderThrowable(Throwable $e, OutputInterface $output): void
    {
        $output->writeln('', OutputInterface::VERBOSITY_QUIET);
        $output->writeln(sprintf('<error> ERROR </error> <caution>%s</caution>', $e->getMessage()), OutputInterface::VERBOSITY_QUIET);
        do {
            $output->writeln(str_repeat('-', 50), OutputInterface::VERBOSITY_QUIET);
            $traces = $e->getTrace();
            $traces[] = [
                'text' => '{main}',
            ];
            $padLength = strlen(count($traces));
            array_unshift($traces, [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'text' => implode(PHP_EOL, [
                    get_debug_type($e) . ':',
                    str_repeat(' ', $padLength + 1) . ' - ' . $e->getMessage(),
                ]),
            ]);
            foreach ($traces as $i => $trace) {
                $order = str($i);
                if (isset($trace['file'])) {
                    $output->writeln(
                        sprintf(
                            '<comment>#%s</comment> [%s:%s]',
                            $order->padLeft($padLength, '0'),
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
                    elseif (isset($trace['text'])) {
                        $output->writeln(
                            sprintf(
                                '%s %s',
                                str_repeat(' ', $padLength + 1),
                                $trace['text'] ?? ''
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
                                $order->padLeft($padLength, '0'),
                                $trace['class'] ?? '',
                                $trace['type'] ?? '',
                                $trace['function'] ?? '',
                                implode(', ', array_map(fn($arg) => sprintf('<comment>%s</comment>', describe_var($arg)), $trace['args'] ?? []))
                            ),
                            OutputInterface::VERBOSITY_QUIET
                        );
                    }
                    elseif (isset($trace['text'])) {
                        $output->writeln(
                            sprintf(
                                '<comment>#%s</comment> %s',
                                $order->padLeft($padLength, '0'),
                                $trace['text'] ?? ''
                            )
                        );
                    }
                    else {
                        $output->writeln(
                            sprintf(
                                '<comment>#%s</comment> %s',
                                $order->padLeft($padLength, '0'),
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
        if ($runningCommand = $this->rootRunningCommand()) {
            $command = $runningCommand->command;
            $output->writeln(sprintf('<caution>Command <comment>[%s]</comment> failed.</caution>', $command::class), OutputInterface::VERBOSITY_QUIET);
            Log::error(sprintf('Command [%s] failed.', $command::class));
        }
    }
}