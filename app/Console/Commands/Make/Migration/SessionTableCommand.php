<?php

namespace App\Console\Commands\Make\Migration;

use Illuminate\Session\Console\SessionTableCommand as BaseSessionTableCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class SessionTableCommand extends BaseSessionTableCommand
{
    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $table = $this->laravel['config']['session.table'];

        $this->replaceMigration($this->createBaseMigration($table), $table);

        $this->info('Migration created successfully.');

        $this->composer->dumpAutoloads();
    }

    /**
     * Create a base migration file for the table.
     */
    protected function createBaseMigration(string $table = 'session'): string
    {
        return $this->laravel['migration.creator']->create(
            'create_' . $table . '_table', $this->laravel->databasePath() . '/migrations'
        );
    }

    /**
     * Replace the generated migration with the job table stub.
     *
     * @throws FileNotFoundException
     */
    protected function replaceMigration(string $path, string $table)
    {
        $stub = str_replace(
            '{{table}}', $table, $this->files->get(base_path('stubs/migration.session.stub'))
        );

        $this->files->put($path, $stub);
    }
}