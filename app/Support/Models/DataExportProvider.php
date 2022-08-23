<?php

namespace App\Support\Models;

use App\Support\Client\DateTimer;
use App\Support\Exports\Export;
use App\Support\Jobs\DataExportJob;
use App\Support\Jobs\QueueableDataExportJob;
use InvalidArgumentException;
use Throwable;

/**
 * @property DataExport|null $model
 * @method DataExport updateWithAttributes(array $attributes = [])
 */
class DataExportProvider extends ModelProvider
{
    public string $modelClass = DataExport::class;

    public function createWithExport(Export $export, string $exportJobClass = QueueableDataExportJob::class): DataExport
    {
        if (!is_a($exportJobClass, DataExportJob::class, true)
            && !is_a($exportJobClass, QueueableDataExportJob::class, true)) {
            throw new InvalidArgumentException('A "DataExportJob" class required.');
        }
        $exportJobClass::dispatch(
            $this->createWithAttributes([
                'name' => $export->getName(),
                'export' => $export,
                'status' => DataExport::STATUS_EXPORTING,
            ])
        );
        return $this->model;
    }

    public function updateExported($file): DataExport
    {
        return $this->updateWithAttributes([
            'file_id' => $this->retrieveKey($file),
            'status' => DataExport::STATUS_EXPORTED,
            'exception' => null,
            'failed_at' => null,
        ]);
    }

    public function updateFailed(?Throwable $e): DataExport
    {
        return $this->updateWithAttributes([
            'status' => DataExport::STATUS_FAILED,
            'exception' => $e,
            'failed_at' => DateTimer::databaseNow(),
        ]);
    }
}
