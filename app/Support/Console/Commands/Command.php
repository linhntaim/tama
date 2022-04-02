<?php

/**
 * Base
 */

namespace App\Support\Console\Commands;

use App\Support\ClassTrait;
use App\Support\Client\Client;
use App\Support\Console\Application;
use Exception;
use Illuminate\Console\Command as BaseCommand;
use Illuminate\Console\Scheduling\ScheduleRunCommand;
use Illuminate\Queue\Console\WorkCommand;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method Application getApplication()
 */
abstract class Command extends BaseCommand
{
    use ClassTrait;

    public const OPTION_OFF_SHOUT_OUT = 'off-shout-out';
    public const PARAMETER_OFF_SHOUT_OUT = '--' . self::OPTION_OFF_SHOUT_OUT;

    private ?bool $canShoutOut = null;

    private ?array $settingsArguments = null;

    protected string|array|null $settings = null;

    protected bool $settingsPermanently = false;

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

    protected function settingsCommanded(): array
    {
        $clientSettingsConfig = config_starter('client.settings');
        $settings = [];
        if (is_string($this->settings)) {
            $settings = $clientSettingsConfig[$this->settings] ?? [];
        }
        elseif (is_array($this->settings)) {
            foreach ($this->settings as $name => $value) {
                $settings[$name] = $value;
            }
        }
        return $settings;
    }

    protected function settingsArgumentsCommanded(): array
    {
        $settingsArguments = [];
        foreach ($this->settingsCommanded() as $name => $value) {
            $settingsArguments["--x-$name"] = $value;
        }
        return $settingsArguments;
    }

    public function settingsArguments(): array
    {
        if (is_null($this->settingsArguments)) {
            $this->settingsArguments = $this->settingsArgumentsCommanded();
            if (!count($this->settingsArguments) || !$this->settingsPermanently) {
                if (!is_null($value = $this->option('x-client'))) {
                    $this->settingsArguments['--x-client'] = $value;
                }
                foreach (array_keys(config_starter('client.settings.default')) as $name) {
                    if (!is_null($value = $this->option("x-$name"))) {
                        $this->settingsArguments["--x-$name"] = $value;
                    }
                }
            }
        }
        return $this->settingsArguments;
    }

    protected function settingsParse()
    {
        $clientSettingsConfig = config_starter('client.settings');
        $settings = $this->settingsCommanded();
        if (!count($settings) || !$this->settingsPermanently) {
            if (!is_null($value = $this->option('x-client'))) {
                $settings = $clientSettingsConfig[$value] ?? [];
            }
            foreach (array_keys($clientSettingsConfig['default']) as $name) {
                if (!is_null($value = $this->option("x-$name"))) {
                    $settings[$name] = $value;
                }
            }
        }
        return $settings;
    }

    /**
     * @throws Exception
     */
    protected function runCommand($command, array $arguments, OutputInterface $output): int
    {
        $arguments[self::PARAMETER_OFF_SHOUT_OUT] = true;
        $arguments = ['command' => $command] + $arguments + $this->settingsArguments();

        $command = $this->resolveCommand($command);
        $input = $this->createInputFromArguments($arguments);
        $this->getApplication()->startRunningCommand($command, $input);
        $notStarterCommand = !($command instanceof Command);
        $canLog = $notStarterCommand
            && !in_array($command::class, config_starter('console.commands.logging_except'));
        if ($canLog) {
            Log::info(sprintf('Command [%s] started.', $command::class));
        }
        $canShoutOut = $notStarterCommand
            && $this->laravel->runningInConsole()
            && !$this->laravel->runningUnitTests()
            && !($arguments[self::PARAMETER_OFF_SHOUT_OUT] ?? false);
        if ($canShoutOut) {
            $this->info(sprintf('Command [%s] started.', $command::class));
            $this->newLine();
        }
        $exitCode = $command->run($input, $output);
        if ($canShoutOut) {
            $this->newLine();
            $this->info(sprintf('Command [%s] ended.', $command::class));
        }
        if ($canLog) {
            Log::info(sprintf('Command [%s] ended.', $command::class));
        }
        $this->getApplication()->endRunningCommand();
        return $exitCode;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (count($settings = $this->settingsParse())) {
            return Client::settingsTemporary($settings, fn() => parent::execute($input, $output));
        }
        return parent::execute($input, $output);
    }

    protected function canShoutOut(): bool
    {
        return is_null($this->canShoutOut)
            ? ($this->canShoutOut = $this->laravel->runningInConsole()
                && !$this->laravel->runningUnitTests()
                && !$this->option(self::OPTION_OFF_SHOUT_OUT))
            : $this->canShoutOut;
    }

    protected function handleBefore(): void
    {
    }

    protected function handleAfter(): void
    {
    }

    public function handle(): int
    {
        Log::info(sprintf('Command [%s] started.', $this->className()));
        if ($this->canShoutOut()) {
            $this->info(sprintf('Command <comment>[%s]</comment> started.', $this->className()));
            $this->newLine();
        }
        $this->handleBefore();
        $exit = $this->handling();
        $this->handleAfter();
        if ($this->canShoutOut()) {
            $this->newLine();
            $this->info(sprintf('Command <comment>[%s]</comment> ended.', $this->className()));
        }
        Log::info(sprintf('Command [%s] ended.', $this->className()));
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