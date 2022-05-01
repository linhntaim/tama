<?php

namespace App\Support\Exports;

use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Filesystem\Filers\Filer;
use App\Support\Http\Resources\ModelResourceTransformer;
use App\Support\Models\ModelProvider;

abstract class ModelCsvExport extends CsvExport
{
    use ModelResourceTransformer;

    protected bool $continuousData = true;

    protected int $perRead;

    protected array $conditions;

    protected bool $more = true;

    protected ModelProvider $modelProvider;

    public function __construct(array $conditions = [], $perRead = 1000)
    {
        $this->conditions = $conditions;
        $this->perRead = $perRead;
        take($this->modelProviderClass(), function ($class) {
            take(new $class, function (ModelProvider $modelProvider) {
                $this->modelProvider = $modelProvider;
            });
        });
    }

    public function conditions(array $conditions): static
    {
        $this->conditions = $conditions;
        return $this;
    }

    public function perRead(mixed $perRead): static
    {
        $this->perRead = $perRead;
        return $this;
    }

    protected abstract function modelProviderClass(): string;

    protected abstract function modelResourceClass(): string;

    protected function exportBefore(Filer $filer)
    {
        parent::exportBefore($filer);
        $this->modelProvider->readStart($this->conditions, $this->perRead);
    }

    /**
     * @throws DatabaseException|Exception
     */
    protected function data(): ?array
    {
        return $this->more
            ? $this->modelResourceTransform($this->modelProvider->read($this->more), $this->modelResourceClass())
            : null;
    }
}