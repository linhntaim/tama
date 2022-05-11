<?php

namespace App\Console\Commands\Setup;

use App\Support\Console\Commands\ForceCommand;
use App\Support\EnvironmentFile;

class EnvCommand extends ForceCommand
{
    protected function handling(): int
    {
        if (file_exists($this->laravel->environmentFilePath()) && !$this->forced()) {
            $this->error('The [.env] file already exists.');
            return $this->exitFailure();
        }
        if (!$this->createEnvironmentFile()) {
            $this->error('The [.env] file cannot be created.');
            return $this->exitFailure();
        }
        $this->info('The [.env] file was created.');
        $this->applyConfiguration();
        return $this->exitSuccess();
    }

    protected function createEnvironmentFile(): bool
    {
        return copy($this->laravel->basePath('.env.example'), $this->laravel->environmentFilePath()) === true;
    }

    protected function applyConfiguration()
    {
        $this->saveConfiguration(
            (($appEnv = $this->choice('Environment?', [
                'Local',
                'Production',
            ], 1)) == 'Production' // prod
                ? [
                    'APP_ENV' => 'production',
                    'APP_DEBUG' => false,
                ]
                : [
                    'APP_ENV' => 'local',
                    'APP_DEBUG' => true,
                ])
            + [
                'APP_NAME' => ($appName = $this->ask('App name?', 'Starter')),
                'APP_ID' => ($appId = $this->ask('App ID?', str($appName)->snake()->toString())),
                'APP_URL' => ($appUrl = $this->ask('App URL?', 'http://localhost')),

                'DB_HOST' => ($dbHost = $this->ask('Database host?', '127.0.0.1')),
                'DB_PORT' => ($dbPort = $this->ask('Database port?', '3306')),
                'DB_DATABASE' => ($dbDatabase = $this->ask('Database name?', $appId)),
                'DB_USERNAME' => ($dbUsername = $this->ask('Database username?', $appId)),
                'DB_PASSWORD' => ($dbPassword = $this->ask('Database password?')),
            ]
        );
        $this->info('The [.env] file was configured.');
    }

    protected function saveConfiguration(array $envs)
    {
        (new EnvironmentFile($this->laravel->environmentFilePath()))
            ->fill($envs)
            ->save();
    }
}
