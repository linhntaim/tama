<?php

/**
 * Base
 */

namespace App\Console\Commands\Setup;

use App\Support\Console\Commands\ForceCommand;
use App\Support\EnvironmentFile;
use App\Support\Exceptions\DatabaseException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use PDO;
use PDOException;
use Symfony\Component\Finder\Finder;

class MigrateCommand extends ForceCommand
{
    protected array $databaseConnection;

    protected array $databaseConnectionWrite;

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected Filesystem $files;

    /**
     * @var Composer
     */
    protected Composer $composer;

    /**
     * Create a new cache table command instance.
     *
     * @param Filesystem $files
     * @param Composer $composer
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct();

        $this->files = $files;
        $this->composer = $composer;
    }

    protected function getDatabaseConfig(string $key)
    {
        return $this->databaseConnectionWrite[$key] ?? $this->databaseConnection[$key];
    }

    /**
     * @throws DatabaseException
     */
    protected function createDatabaseConnector(): PDO
    {
        return match ($this->databaseConnection['driver']) {
            'mysql' => $this->createMySqlDatabaseConnector(),
        };
    }

    /**
     * @throws DatabaseException
     */
    protected function createMySqlDatabaseConnector(): PDO
    {
        try {
            return new PDO(
                sprintf(
                    'mysql:host=%s;port:%d',
                    $this->getDatabaseConfig('host'),
                    $this->getDatabaseConfig('port')
                ),
                $this->getDatabaseConfig('username'),
                $this->getDatabaseConfig('password'),
                $this->getDatabaseConfig('options')
            );
        }
        catch (PDOException $exception) {
            throw DatabaseException::from($exception);
        }
    }

    protected function handleBefore(): void
    {
        $this->databaseConnection = config('database.connections.' . config('database.default'));
        $this->databaseConnectionWrite = $this->databaseConnection['write'] ?? $this->databaseConnection;

        parent::handleBefore();
    }

    /**
     * @throws DatabaseException
     */
    protected function whenForced()
    {
        $this->uninstallDatabase();
    }

    /**
     * @throws FileNotFoundException
     */
    protected function handling(): int
    {
        foreach ([
                     'migrateDatabase',
                     'migrateTables',
                     'migrateSeed',
                 ] as $method) {
            $this->warn(sprintf('Migrate %s ...', lcfirst(substr($method, 7))));
            if (!$this->{$method}()) {
                $this->error('Migration failed.');
                return $this->exitFailure();
            }
            $this->info(sprintf('%s migrated.', substr($method, 7)));
        }
        return $this->exitSuccess();
    }

    /**
     * @throws DatabaseException
     */
    protected function migrateDatabase(): bool
    {
        $databaseConnector = $this->createDatabaseConnector();
        return match ($this->databaseConnection['driver']) {
            'mysql' => $this->migrateMySqlDatabase($databaseConnector),
        };
    }

    /**
     * @throws DatabaseException
     */
    protected function migrateMySqlDatabase(PDO $connector): bool
    {
        try {
            return ($query = $connector->prepare(
                    sprintf('create database if not exists `%s`', $this->getDatabaseConfig('database'))
                )) !== false
                && $query->execute();
        }
        catch (PDOException $exception) {
            throw DatabaseException::from($exception);
        }
    }

    /**
     * @throws DatabaseException
     */
    protected function uninstallDatabase(): bool
    {
        $databaseConnector = $this->createDatabaseConnector();
        return match ($this->databaseConnection['driver']) {
            'mysql' => $this->uninstallMySqlDatabase($databaseConnector),
        };
    }

    /**
     * @throws DatabaseException
     */
    protected function uninstallMySqlDatabase(PDO $connector): bool
    {
        try {
            return ($query = $connector->prepare(
                    sprintf('drop database if exists `%s`', $this->getDatabaseConfig('database'))
                )) !== false
                && $query->execute();
        }
        catch (PDOException $exception) {
            throw DatabaseException::from($exception);
        }
    }

    /**
     * @throws FileNotFoundException
     */
    protected function migrateTables(): bool
    {
        return $this->migrateExtraTables()
            && $this->call('migrate') == self::SUCCESS;
    }

    /**
     * Cache/Queue/Session
     *
     * @return bool
     * @throws FileNotFoundException
     */
    protected function migrateExtraTables(): bool
    {
        $migrationTables = [];
        foreach (Finder::create()->in(database_path('migrations'))->files() as $file) {
            if (preg_match('/(create|update)_([a-z0-9_]+)_table\.php/', ($basename = $file->getBasename()), $matches) === 1 && isset($matches[2])) {
                $migrationTables[$basename] = $matches[2];
            }
        }
        $environmentFile = new EnvironmentFile($this->laravel->environmentFilePath());
        if ($environmentFile->filled('CACHE_DRIVER', $cacheDriver) && $cacheDriver === 'database') {
            $this->comment('Cache uses database.');
            if (in_array(config('cache.stores.database.table'), $migrationTables)) {
                $this->info('Migration created already.');
            }
            else {
                if ($this->call('cache:table') != self::SUCCESS) {
                    return false;
                }
            }
        }
        if ($environmentFile->filled('SESSION_DRIVER', $sessionDriver) && $sessionDriver === 'database') {
            $this->comment('Session uses database.');
            if (in_array(config('session.table'), $migrationTables)) {
                $this->info('Migration created already.');
            }
            else {
                if ($this->call('session:table') != self::SUCCESS) {
                    return false;
                }
            }
        }
        if ($environmentFile->filled('QUEUE_CONNECTION', $queueConnection) && $queueConnection === 'database') {
            $this->comment('Queue uses database.');
            if (in_array(config('queue.connections.database.table'), $migrationTables)) {
                $this->info('Migration created already.');
            }
            else {
                if ($this->call('queue:table') != self::SUCCESS) {
                    return false;
                }
            }
        }
        if (($searched = array_search('failed_jobs', $migrationTables)) !== false
            && ($table = config('queue.failed.table')) != 'failed_jobs') {
            $this->comment('Failed jobs table changes.');
            $toMigrationFile = database_path(join_paths('migrations', str_replace('failed_jobs', $table, $searched)));
            $fromMigrationFile = database_path(join_paths('migrations', $searched));
            $this->files->put(
                $toMigrationFile,
                str_replace('failed_jobs', $table, $this->files->get($fromMigrationFile))
            );
            $this->files->delete($fromMigrationFile);
            $this->composer->dumpAutoloads();
            $this->info('Migrate changed successfully.');
        }
        return true;
    }

    protected function migrateSeed(): bool
    {
        return true;
    }
}
