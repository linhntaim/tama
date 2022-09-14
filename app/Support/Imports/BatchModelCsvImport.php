<?php

namespace App\Support\Imports;

use App\Support\Exceptions\FileException;
use App\Support\Filesystem\Filers\CsvFiler;
use App\Support\Filesystem\Filers\Filer;

abstract class BatchModelCsvImport extends ModelCsvImport
{
    protected int $perWrite;

    protected bool $writeIgnore = false;

    public function __construct(int $perWrite = 1000, bool $writeIgnore = false)
    {
        parent::__construct();

        $this->perWrite = $perWrite;
        $this->writeIgnore = $writeIgnore;
    }

    public function perWrite(int $perWrite): static
    {
        $this->perWrite = $perWrite;
        return $this;
    }

    public function writeIgnore(bool $writeIgnore): static
    {
        $this->writeIgnore = $writeIgnore;
        return $this;
    }

    /**
     * @param CsvFiler $filer
     * @return void
     * @throws FileException
     */
    protected function importBefore(Filer $filer): void
    {
        parent::importBefore($filer);
        $this->modelProvider->writeStart($this->perWrite, $this->writeIgnore);
    }

    /**
     * @param CsvFiler $filer
     * @return void
     */
    protected function importAfter(Filer $filer): void
    {
        $this->modelProvider->writeEnd();
        parent::importAfter($filer);
    }

    protected function dataImport(array $data): void
    {
        $this->modelProvider->write($data);
    }
}
