<?php

namespace App\Support\Jobs;

use App\Support\Filesystem\Filers\Filer;
use App\Support\Imports\Import;
use App\Support\Models\DataImport;
use App\Support\Models\DataImportProvider;
use App\Support\Models\File;
use App\Support\Models\FileProvider;
use Throwable;

trait BaseDataImportJob
{
    protected DataImport $dataImport;

    protected Import $import;

    protected File $file;

    protected bool $fileCloned = false;

    public function __construct(DataImport $dataImport)
    {
        parent::__construct();

        $this->dataImport = $dataImport;
    }

    protected function setImport(Import $import): static
    {
        $this->import = $import;
        return $this;
    }

    protected function getImport(): Import
    {
        return $this->import ?? $this->setImport($this->dataImport->import)->import;
    }

    protected function handling(): void
    {
        if (!isset($this->file)) {
            $this->file = $this->dataImport->file;
            if (!($filer = Filer::from($this->file))->internal()) {
                $this->file = (new FileProvider())->createWithFiler($filer->copyToLocal());
                $this->fileCloned = true;
            }
        }
        $import = $this->getImport();
        $import($this->file);
        if ($import->chunkEnded()) {
            (new DataImportProvider())
                ->withModel($this->dataImport)
                ->updateImported();
            if ($this->fileCloned) {
                (new FileProvider())->withModel($this->file)->delete();
            }
        }
        else {
            self::dispatchWith(
                function ($job) {
                    $job->import = $this->import;
                    $job->file = $this->file;
                    $job->fileCloned = $this->fileCloned;
                    return $job;
                },
                $this->dataImport
            );
        }
    }

    public function failed(?Throwable $e = null): void
    {
        (new DataImportProvider())
            ->withModel($this->dataImport)
            ->updateFailed($e);
    }
}
