<?php

namespace App\Support\Models;

use App\Support\Client\DateTimer;
use App\Support\Imports\Import;
use App\Support\Jobs\DataImportJob;
use App\Support\Jobs\QueueableDataImportJob;
use InvalidArgumentException;
use Throwable;

/**
 * @property DataImport|null $model
 * @method DataImport updateWithAttributes(array $attributes = [])
 */
class DataImportProvider extends ModelProvider
{
    public string $modelClass = DataImport::class;

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

    public function updateImported(): DataImport
    {
        return $this->updateWithAttributes([
            'status' => DataImport::STATUS_IMPORTED,
            'exception' => null,
            'failed_at' => null,
        ]);
    }

    public function updateFailed(?Throwable $e): DataImport
    {
        return $this->updateWithAttributes([
            'status' => DataImport::STATUS_FAILED,
            'exception' => $e,
            'failed_at' => DateTimer::databaseNow(),
        ]);
    }
}
