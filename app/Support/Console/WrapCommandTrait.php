<?php

namespace App\Support\Console;

use App\Support\App;
use App\Support\Client\Client;
use App\Support\Client\Settings;
use App\Support\Console\Application as Artisan;
use App\Support\Console\Commands\Command;
use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

trait WrapCommandTrait
{
    protected function wrapCanLog(SymfonyCommand $command, InputInterface $input): bool
    {
        return !in_array($command::class, config_starter('console.commands.logging_except'));
    }

    protected function wrapCanShoutOut(SymfonyCommand $command, InputInterface $input): bool
    {
        return App::runningSolelyInConsole()
            && !($input->hasParameterOption(Command::PARAMETER_OFF_SHOUT_OUT));
    }

    protected function wrapRunning(
        Container       $app,
        Artisan         $artisan,
        SymfonyCommand  $command,
        InputInterface  $input,
        OutputInterface $output,
        Closure         $runCallback)
    {
        $artisan->startRunningCommand($command, $input);
        if ($canLog = $this->wrapCanLog($command, $input)) {
            Log::info(sprintf('Command [%s] started.', $command::class));
        }
        if ($canShoutOut = $this->wrapCanShoutOut($command, $input)) {
            $output->writeln(sprintf('<info>Command <comment>[%s]</comment> started.</info>', $command::class), OutputInterface::VERBOSITY_QUIET);
            $output->writeln('', OutputInterface::VERBOSITY_QUIET);
        }

        $runCallbackWithDebug = !App::runningInDebug()
        && (App::runningSolelyInConsole() || config_starter('app.debug_from_request'))
        && $input->hasParameterOption(Command::PARAMETER_DEBUG)
            ? function ($command, $input, $output) use ($runCallback) {
                return with_debug($runCallback, $command, $input, $output);
            }
            : $runCallback;

        $settings = [];
        if ($client = $input->getParameterOption(Command::PARAMETER_CLIENT, null)) {
            $settings = Settings::parseConfig($client);
        }
        foreach (Settings::names() as $name) {
            if ($value = $input->getParameterOption("--x-$name", null)) {
                $settings[$name] = $value;
            }
        }
        if (count($settings)) {
            if ($command instanceof Command) {
                $command->setForcedInternalSettings($settings);
            }
            $exitCode = Client::settingsTemporary($settings, function () use ($runCallbackWithDebug, $command, $input, $output) {
                return $runCallbackWithDebug($command, $input, $output);
            });
        }
        else {
            $exitCode = $runCallbackWithDebug($command, $input, $output);
        }
        if ($canShoutOut) {
            $output->writeln('', OutputInterface::VERBOSITY_QUIET);
            $output->writeln(sprintf('<info>Command <comment>[%s]</comment> ended.</info>', $command::class), OutputInterface::VERBOSITY_QUIET);
        }
        if ($canLog) {
            Log::info(sprintf('Command [%s] ended.', $command::class));
        }
        $artisan->endRunningCommand();
        return $exitCode;
    }

    protected function wrapException(?SymfonyCommand $command, ?InputInterface $input, OutputInterface $output, Throwable $e)
    {
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
                                json_encode_readable($trace)
                            ),
                            OutputInterface::VERBOSITY_QUIET
                        );
                    }
                }
            }
        }
        while ($e = $e->getPrevious());

        if ($command) {
            if ($this->wrapCanShoutOut($command, $input)) {
                $output->writeln('', OutputInterface::VERBOSITY_QUIET);
                $output->writeln(sprintf('<caution>Command <comment>[%s]</comment> failed.</caution>', $command::class), OutputInterface::VERBOSITY_QUIET);
            }
            if ($this->wrapCanLog($command, $input)) {
                Log::error(sprintf('Command [%s] failed.', $command::class));
            }
        }
    }
}