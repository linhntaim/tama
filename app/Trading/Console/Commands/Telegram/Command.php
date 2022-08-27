<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Support\Console\Commands\Command as BaseCommand;
use App\Trading\Services\Telegram\Update as TelegramUpdate;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

abstract class Command extends BaseCommand
{
    private array $synopsisForDescriptor = [];

    private InputDefinition $definitionForDescriptor;

    protected TelegramUpdate $telegramUpdate;

    protected function getDefaultOptions(): array
    {
        return [
            new InputOption('telegram-update', null, InputOption::VALUE_REQUIRED),
        ];
    }

    protected function handleBefore(): void
    {
        if (is_null($telegram = $this->option('telegram-update'))) {
            throw new InvalidArgumentException('Telegram update option must be provided.');
        }
        $this->telegramUpdate = new TelegramUpdate(json_decode_array(base64_decode($telegram)));

        parent::handleBefore();
    }

    public function getSynopsisForDescriptor(bool $short = false): string
    {
        $key = $short ? 'short' : 'long';

        if (!isset($this->synopsisForDescriptor[$key])) {
            $this->synopsisForDescriptor[$key] = trim(sprintf('/%s %s', substr($this->name, strlen('telegram:')), $this->getDefinitionForDescriptor()->getSynopsis($short)));
        }

        return $this->synopsisForDescriptor[$key];
    }

    public function getDefinitionForDescriptor(): InputDefinition
    {
        return $this->definitionForDescriptor ?? $this->definitionForDescriptor = modify(new InputDefinition(), function (InputDefinition $inputDefinition) {
            $inputDefinition->setArguments($this->getNativeDefinition()->getArguments());
            $inputDefinition->setOptions(array_filter($this->getNativeDefinition()->getOptions(), static function (InputOption $option) {
                return !in_array($option->getName(), [
                    'telegram-update',
                    'x-debug',
                    'off-shout-out',
                    'x-client',
                    'x-locale',
                    'x-country',
                    'x-timezone',
                    'x-currency',
                    'x-number_format',
                    'x-long_date_format',
                    'x-short_date_format',
                    'x-long_time_format',
                    'x-short_time_format',
                ]);
            }));
            return $inputDefinition;
        });
    }
}
