<?php

namespace App\Console\Commands\MailHog;

use App\Support\EnvironmentFile;
use App\Support\Exceptions\ShellException;

class StartCommand extends Command
{
    protected function handling(): int
    {
        if (!is_file($this->binFile)) {
            $this->call('mail-hog:setup');
        }
        if (!is_file($this->binFile)) {
            $this->error('Bin file not found');
            return $this->exitFailure();
        }
        if ($this->confirm('Use "mailhog" as the mailer?', true)) {
            (new EnvironmentFile($this->laravel->environmentFilePath()))
                ->fill([
                    'MAIL_MAILER' => 'smtp',
                    'MAIL_HOST' => '127.0.0.1',
                    'MAIL_PORT' => '1025',
                ])
                ->save();
        }
        if (method_exists($this, $method = sprintf('start%s', PHP_OS_FAMILY))) {
            $this->{$method}();
        }
        return $this->exitSuccess();
    }

    /**
     * @throws ShellException
     */
    protected function startWindows(): void
    {
        $this->handleShell(sprintf('"%s"', $this->binFile), function ($type, $data) {
            $this->text(str_replace('http://0.0.0.0', 'http://127.0.0.1', $data));
        });
    }
}
