<?php

namespace App\Support\Imports;

use App\Support\Exceptions\FileException;
use App\Support\Filesystem\Filers\CsvFiler;

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

    public function perWrite(mixed $perWrite): static
    {
        $this->perWrite = $perWrite;
        return $this;
    }

    public function writeIgnore(mixed $writeIgnore): static
    {
        $this->writeIgnore = $writeIgnore;
        return $this;
    }

    /**
     * @param CsvFiler $filer
     * @throws FileException
     */
    protected function importBefore($filer)
    {
        parent::importBefore($filer);
        $this->modelProvider->writeStart($this->perWrite, $this->writeIgnore);
    }

    /**
     * @param CsvFiler $filer
     */
    protected function importAfter($filer)
    {
        $this->modelProvider->writeEnd();
        parent::importAfter($filer);
    }

    protected function dataImport(array $data)
    {
        $this->modelProvider->write($data);
    }
}
