<?php

namespace App\Support\Exports;

use App\Support\Exceptions\FileException;
use App\Support\Filesystem\Filers\CsvFiler;

abstract class CsvExport extends Export
{
    protected ?array $headers = null;

    protected array $data;

    protected function headers(): ?array
    {
        return $this->headers;
    }

    protected function data()
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
    protected function exportBefore($filer)
    {
        parent::exportBefore($filer);
        if ($this->dataIndex == -1 && ($headers = $this->headers())) {
            $filer->writeln($headers);
        }
    }
}
