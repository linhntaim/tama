<?php

/**
 * Base
 */

namespace App\Console\Commands\Setup;

use App\Exceptions\DatabaseException;
use App\Support\Console\Commands\ForceCommand;
use PDO;
use PDOException;

class MigrateCommand extends ForceCommand
{
    protected array $databaseConnection;

    protected array $databaseConnectionWrite;

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

    private function migrateTables(): bool
    {
        return $this->call('migrate') == self::SUCCESS;
    }

    protected function migrateSeed(): bool
    {
        return true;
    }
}
