<?php

/**
 * Base
 */

namespace App\Support\Console\Commands;

use App\Support\ClassTrait;
use App\Support\Client\Client;
use App\Support\Client\InternalSettingsTrait;
use App\Support\Console\Application;
use Illuminate\Console\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method Application getApplication()
 */
abstract class Command extends BaseCommand
{
    use ClassTrait, WrapCommandTrait, InternalSettingsTrait;

    public const OPTION_OFF_SHOUT_OUT = 'off-shout-out';
    public const PARAMETER_OFF_SHOUT_OUT = '--' . self::OPTION_OFF_SHOUT_OUT;

    public function __construct()
    {
        if (isset($this->signature)) {
            if (trim($this->signature)[0] == '{') {
                $this->signature = $this->generateName() . ' ' . $this->signature;
            }
        }
        else {
            if (!isset($this->name)) {
                $this->name = $this->generateName();
            }
        }

        parent::__construct();
    }

    protected function generateName(): string
    {
        return implode(
            ':',
            array_map(
                function ($name) {
                    return str($name)->snake('-')->toString();
                },
                (function (array $names) {
                    if (($count = count($names)) == 1 && $names[0] == '') {
                        return ['command'];
                    }
                    if ($count >= 2) {
                        $l1 = array_pop($names);
                        while (($l2 = array_pop($names)) && $l2 == $l1) {
                        }
                        array_push($names, ...array_filter([$l2, $l1]));
                    }
                    return $names;
                })(explode('\\', rtrim(preg_replace('/^App\\\\Console\\\\Commands\\\\|Command$/', '', $this->className()), '\\')))
            )
        );
    }

    protected function configure()
    {
        parent::configure();
        $this->specifyDefaultParameters();
    }

    protected function specifyDefaultParameters()
    {
        foreach ($this->getDefaultArguments() as $arguments) {
            if ($arguments instanceof InputArgument) {
                $this->getDefinition()->addArgument($arguments);
            }
            else {
                $this->addArgument(...array_values($arguments));
            }
        }

        foreach ($this->getDefaultOptions() as $options) {
            if ($options instanceof InputOption) {
                $this->getDefinition()->addOption($options);
            }
            else {
                $this->addOption(...array_values($options));
            }
        }
    }

    protected function getDefaultArguments(): array
    {
        return [];
    }

    protected function getDefaultOptions(): array
    {
        return [];
    }

    protected function runCommand($command, array $arguments, OutputInterface $output): int
    {
        $arguments[self::PARAMETER_OFF_SHOUT_OUT] = true;

        return $this->wrapRunning(
            $this->laravel,
            $this->getApplication(),
            $this->resolveCommand($command),
            $this->createInputFromArguments(['command' => $command] + $arguments),
            $output,
            function ($command, $input, $output) {
                return $command->run($input, $output);
            }
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this
            ->setForcedSettings(count($this->getInternalSettings()) ? Client::settings()->toArray() : [])
            ->withInternalSettings(function () use ($input, $output) {
                return parent::execute($input, $output);
            });
    }

    protected function handleBefore(): void
    {
    }

    protected function handleAfter(): void
    {
    }

    public function handle(): int
    {
        $this->handleBefore();
        $exit = $this->handling();
        $this->handleAfter();
        return $exit;
    }

    protected abstract function handling(): int;

    protected function exitSuccess(): int
    {
        return self::SUCCESS;
    }

    protected function exitFailure(): int
    {
        return self::FAILURE;
    }

    protected function exitInvalid(): int
    {
        return self::INVALID;
    }
}