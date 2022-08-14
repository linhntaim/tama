<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Output\BufferedOutput;

class HelpCommand extends Command
{
    public $signature = '{command_name? : Command name (eg. help, ping, ...).}';

    protected $description = 'Show the guide.';

    protected function handling(): int
    {
        $output = new BufferedOutput;
        if (is_null($commandName = $this->argument('command_name'))) {
            $this->describeAvailableCommands($output);
            $output->writeln('');
            $this->describe($output, $this);
        }
        else {
            $this->describe($output, $this->getApplication()->find('telegram:' . $commandName));
        }
        ConsoleNotification::send(new TelegramUpdateNotifiable($this->telegramUpdate), $output->fetch());
        return $this->exitSuccess();
    }

    protected function describeAvailableCommands(BufferedOutput $output)
    {
        $output->writeln('Available commands:');
        $commands = collect($this->getApplication()->findByNamespaces('telegram'))
            ->map(function (Command $command) {
                return [
                    'name' => sprintf('/%s', substr($command->getName(), strlen('telegram:'))),
                    'description' => $command->getDescription(),
                ];
            });
        $maxNameLength = $commands
            ->pluck('name')
            ->map(function ($name) {
                return strlen($name);
            })
            ->max();
        foreach ($commands as $command) {
            $output->writeln(
                sprintf(
                    '  <comment>%s</comment>%s%s', $command['name'],
                    str_repeat(' ', $maxNameLength - strlen($command['name']) + 2),
                    $command['description']
                )
            );
        }
    }

    protected function describe(BufferedOutput $output, $command)
    {
        $helper = (new DescriptorHelper())->register('telegram_txt', new TextDescriptor());
        $helper->describe($output, $command, [
            'format' => 'telegram_txt',
        ]);
    }
}
