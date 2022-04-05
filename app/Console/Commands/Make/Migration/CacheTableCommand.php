<?php

namespace App\Console\Commands\Make\Migration;

use Illuminate\Cache\Console\CacheTableCommand as BaseCacheTableCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class CacheTableCommand extends BaseCacheTableCommand
{
    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $table = $this->laravel['config']['cache.stores.database.table'];
        $lockTable = $this->laravel['config']['cache.stores.database.lock_table'] ?? 'cache_locks';

        $this->replaceMigration($this->createBaseMigration($table), $table, $lockTable);

        $this->info('Migration created successfully.');

        $this->composer->dumpAutoloads();
    }

    /**
     * Create a base migration file for the table.
     */
    protected function createBaseMigration(string $table = 'cache'): string
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
    protected function replaceMigration(string $path, string $table, string $lockTable)
    {
        $stub = str_replace(
            ['{{table}}', '{{lock_table}}'], [$table, $lockTable], $this->files->get(base_path('stubs/migration.cache.stub'))
        );

        $this->files->put($path, $stub);
    }
}