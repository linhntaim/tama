<?php

namespace App\Support\Imports;

use App\Support\Database\Concerns\DatabaseTransaction;
use App\Support\Exceptions\FileException;
use App\Support\Filesystem\Filers\CsvFiler;
use App\Support\Filesystem\Filers\Filer;
use App\Support\Models\ModelProvider;
use Throwable;

abstract class ModelCsvImport extends CsvImport
{
    use DatabaseTransaction;

    // TODO: Remove $modelProvider
    protected ModelProvider $modelProvider;

    protected array $attributeKeyMap = [];

    public function __construct()
    {
        tap($this->modelProviderClass(), function ($class) {
            tap(new $class, function (ModelProvider $modelProvider) {
                $this->modelProvider = $modelProvider;
            });
        });
    }

    abstract protected function modelProviderClass(): string;

    /**
     * @param CsvFiler $filer
     * @return void
     * @throws Throwable
     * @throws FileException
     */
    protected function import(Filer $filer): void
    {
        $this->transactionStart();
        try {
            parent::import($filer);
            $this->transactionComplete();
        }
        catch (Throwable $exception) {
            $this->transactionAbort();
            throw $exception;
        }
    }

    protected function attributeKeyMap(): array
    {
        return $this->attributeKeyMap;
    }

    protected function dataMap(array $data): array
    {
        $attributes = [];
        foreach ($this->attributeKeyMap() as $dataKey => $attributeKey) {
            $attributes[$attributeKey] = $data[$dataKey] ?? null;
        }
        return $attributes;
    }

    protected function dataImport(array $data): void
    {
        $this->modelProvider->createWithAttributes($data);
    }
}
