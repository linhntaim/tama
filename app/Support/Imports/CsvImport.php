<?php

namespace App\Support\Imports;

use App\Support\Exceptions\FileException;
use App\Support\Filesystem\Filers\CsvFiler;
use App\Support\Filesystem\Filers\Filer;
use Illuminate\Validation\ValidationException;

/**
 * @method array|null data(CsvFiler $filer)
 * @method void import(CsvFiler $filer)
 */
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
     * @return void
     * @throws FileException
     */
    protected function importBefore(Filer $filer): void
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
    protected function dataValidate(array $data): void
    {
        $this->validateData($data, $this->dataValidationRules());
    }

    protected function dataMap(array $data): array
    {
        return $data;
    }

    /**
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    protected function store($data): void
    {
        tap($this->dataMap($data), function (array $data) {
            $this->dataValidate($data);
            $this->dataImport($data);
        });
    }

    abstract protected function dataImport(array $data): void;
}
