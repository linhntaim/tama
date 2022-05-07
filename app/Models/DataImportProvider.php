<?php

namespace App\Models;

use App\Jobs\DataImportJob;
use App\Jobs\QueueableDataImportJob;
use App\Support\Client\DateTimer;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Imports\Import;
use App\Support\Models\ModelProvider;
use InvalidArgumentException;
use Throwable;

/**
 * @property DataImport|null $model
 * @method DataImport updateWithAttributes(array $attributes = [])
 */
class DataImportProvider extends ModelProvider
{
    public string $modelClass = DataImport::class;

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function createWithImport(File $file, Import $import, string $importJobClass = QueueableDataImportJob::class): DataImport
    {
        if (!is_a($importJobClass, DataImportJob::class, true)
            && !is_a($importJobClass, QueueableDataImportJob::class, true)) {
            throw new InvalidArgumentException('A "DataImportJob" class required.');
        }
        $importJobClass::dispatch(
            $this->createWithAttributes([
                'file_id' => $this->retrieveKey($file),
                'name' => $import->getName(),
                'import' => $import,
                'status' => DataImport::STATUS_IMPORTING,
            ])
        );
        return $this->model;
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function updateImported(): DataImport
    {
        return $this->updateWithAttributes([
            'status' => DataImport::STATUS_IMPORTED,
            'exception' => null,
            'failed_at' => null,
        ]);
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function updateFailed(?Throwable $e): DataImport
    {
        return $this->updateWithAttributes([
            'status' => DataImport::STATUS_FAILED,
            'exception' => $e,
            'failed_at' => DateTimer::databaseNow(),
        ]);
    }
}
