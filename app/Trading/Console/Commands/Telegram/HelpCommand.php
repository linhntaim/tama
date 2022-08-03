<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\Telegram\TextNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;
use App\Trading\Telegram\MarkdownConsole;
use App\Trading\Telegram\MarkdownText;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Output\BufferedOutput;

class HelpCommand extends Command
{
    public $signature = '{command_name? : Command name (eg. help, hello, ...).}';

    protected function handling(): int
    {
        $output = new BufferedOutput;
        if (is_null($commandName = $this->argument('command_name'))) {
            $output->writeln('Available commands:');
            $availableCommands = [
                '/help' => 'Help',
                '/ping' => 'Check if the bot is responsible (alias: /hello)',
                '/trades' => 'Get latest possible trades',
            ];
            $maxNameLength = max(array_map(fn($key) => strlen($key), array_keys($availableCommands)));
            foreach ($availableCommands as $name => $desc) {
                $output->writeln(sprintf('  <comment>%s</comment>%s%s.', $name, str_repeat(' ', $maxNameLength - strlen($name) + 2), $desc));
            }
            $output->writeln('');
            $this->describe($output, $this);
        }
        else {
            $this->describe($output, $this->getApplication()->find('telegram:' . $commandName));
        }
        ConsoleNotification::send(new TelegramUpdateNotifiable($this->telegramUpdate), $output->fetch());
        return $this->exitSuccess();
    }

    protected function describe(BufferedOutput $output, $command)
    {
        $helper = (new DescriptorHelper())->register('telegram_txt', new TextDescriptor());
        $helper->describe($output, $command, [
            'format' => 'telegram_txt',
        ]);
    }
}
