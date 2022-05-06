<?php

namespace App\Support\Imports;

use App\Support\Exceptions\FileException;
use App\Support\Filesystem\Filers\CsvFiler;
use Illuminate\Validation\ValidationException;

abstract class CsvImport extends Import
{
    protected bool $hasHeaders = true;

    protected bool|array $withHeaders = false;

    protected function filerClass(): string
    {
        return CsvFiler::class;
    }

    /**
     * @param CsvFiler $filer
     * @throws FileException
     */
    protected function importBefore($filer)
    {
        parent::importBefore($filer);
        $filer
            ->hasHeaders($this->hasHeaders)
            ->withHeaders($this->withHeaders);
    }

    protected function dataValidationRules(): array
    {
        return [];
    }

    /**
     * @throws ValidationException
     */
    protected function dataValidate(array $data)
    {
        $this->validateData($data, $this->dataValidationRules());
    }

    protected function dataMap(array $data): array
    {
        return $data;
    }

    /**
     * @throws ValidationException
     */
    protected function store($data)
    {
        take($this->dataMap($data), function (array $data) {
            $this->dataValidate($data);
            $this->dataImport($data);
        });
    }

    protected abstract function dataImport(array $data);
}
