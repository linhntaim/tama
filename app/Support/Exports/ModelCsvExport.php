<?php

namespace App\Support\Exports;

use App\Support\Http\Resources\Concerns\ResourceTransformer;
use App\Support\Models\ModelProvider;
use Illuminate\Database\Eloquent\Collection;

abstract class ModelCsvExport extends CsvExport
{
    use ResourceTransformer;

    protected string $sortBy;

    protected bool $sortAscending;

    protected array $conditions;

    protected int $perRead = 1000;

    protected int $skipDefault = 0;

    protected int $read = 0;

    protected bool $more = true;

    public function __construct(array $conditions = [], string $sortBy = 'id', bool $sortAscending = true, int $perRead = 1000)
    {
        $this->data = [];
        $this->sortBy = $sortBy;
        $this->sortAscending = $sortAscending;
        $this->conditions = $conditions;
        $this->perRead = $perRead;
    }

    public function sort(string $by, bool $ascending = true): static
    {
        $this->sortBy = $by;
        $this->sortAscending = $ascending;
        return $this;
    }

    public function conditions(array $conditions): static
    {
        $this->conditions = $conditions;
        return $this;
    }

    public function perRead(int $perRead): static
    {
        $this->perRead = $perRead;
        return $this;
    }

    protected abstract function modelProviderClass(): string;

    protected abstract function modelResourceClass(): string;

    protected function modelProvider(): ModelProvider
    {
        $class = $this->modelProviderClass();
        return new $class;
    }

    protected function exportBefore($filer)
    {
        parent::exportBefore($filer);
        $this->skipDefault = $this->dataIndex + 1;
        $this->read = 0;
    }

    protected function prepareData()
    {
        if ($this->more) {
            $this->data = $this->resourceTransform(
                with(
                    $this->modelProvider()
                        ->sort($this->sortBy, $this->sortAscending)
                        ->limit($this->perRead + 1, (++$this->read - 1) * $this->perRead + $this->skipDefault)
                        ->all($this->conditions),
                    function (Collection $models) {
                        if ($this->more = $models->count() > $this->perRead) {
                            $models->pop();
                        }
                        return $models;
                    }
                ),
                $this->modelResourceClass()
            );
        }
    }

    protected function data()
    {
        $this->prepareData();
        return parent::data();
    }
}
