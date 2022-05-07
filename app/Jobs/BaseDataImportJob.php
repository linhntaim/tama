<?php

namespace App\Jobs;

use App\Models\DataImport;
use App\Models\DataImportProvider;
use App\Models\File;
use App\Models\FileProvider;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Filesystem\Filers\Filer;
use App\Support\Imports\Import;
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

    /**
     * @throws DatabaseException
     * @throws Exception
     * @throws Throwable
     */
    protected function handling()
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

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    public function failed(?Throwable $e = null)
    {
        (new DataImportProvider())
            ->withModel($this->dataImport)
            ->updateFailed($e);
    }
}
