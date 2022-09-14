<?php

namespace App\Support\Exports;

use App\Support\Exceptions\FileException;
use App\Support\Filesystem\Filers\CsvFiler;
use App\Support\Filesystem\Filers\Filer;

abstract class CsvExport extends Export
{
    /**
     * @var string[]|null
     */
    protected ?array $headers = null;

    /**
     * @var array[]
     */
    protected array $data;

    /**
     * @return string[]|null
     */
    protected function headers(): ?array
    {
        return $this->headers;
    }

    protected function data(): ?array
    {
        return array_shift($this->data);
    }

    protected function filerClass(): string
    {
        return CsvFiler::class;
    }

    /**
     * @param CsvFiler $filer
     * @throws FileException
     */
    protected function exportBefore(Filer $filer): void
    {
        parent::exportBefore($filer);
        if ($this->dataIndex === -1 && ($headers = $this->headers())) {
            $filer->writeln($headers);
        }
    }
}
