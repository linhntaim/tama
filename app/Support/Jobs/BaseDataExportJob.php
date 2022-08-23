<?php

namespace App\Support\Jobs;

use App\Support\Exports\Export;
use App\Support\Models\DataExport;
use App\Support\Models\DataExportProvider;
use App\Support\Models\File;
use App\Support\Models\FileProvider;
use Throwable;

trait BaseDataExportJob
{
    protected DataExport $dataExport;

    protected Export $export;

    protected File $file;

    public function __construct(DataExport $dataImport)
    {
        parent::__construct();

        $this->dataExport = $dataImport;
    }

    protected function setExport(Export $export): static
    {
        $this->export = $export;
        return $this;
    }

    protected function getExport(): Export
    {
        return $this->export ?? $this->setExport($this->dataExport->export)->export;
    }

    protected function export(): bool
    {
        $export = $this->getExport();

        $doesntHaveFile = !isset($this->file);
        $filer = $doesntHaveFile
            ? $export()
            : $export($this->file);
        $exportCompleted = $export->chunkEnded();
        $this->file = $doesntHaveFile
            ? (new FileProvider())
                ->enablePublish($exportCompleted)
                ->createWithFiler($filer, $export->getName())
            : (new FileProvider())
                ->withModel($this->file)
                ->enablePublish($exportCompleted)
                ->updateWithFiler($filer);

        return $exportCompleted;
    }

    protected function handling()
    {
        if ($this->export()) {
            (new DataExportProvider())
                ->withModel($this->dataExport)
                ->updateExported($this->file);
        }
        else {
            self::dispatchWith(
                function ($job) {
                    $job->export = $this->export;
                    $job->file = $this->file;
                    return $job;
                },
                $this->dataExport
            );
        }
    }

    public function failed(?Throwable $e = null)
    {
        (new DataExportProvider())
            ->withModel($this->dataExport)
            ->updateFailed($e);
    }
}
