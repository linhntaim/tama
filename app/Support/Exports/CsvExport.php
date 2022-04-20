<?php

namespace App\Support\Exports;

use App\Support\Exceptions\FileException;
use App\Support\Filesystem\Filers\CsvFiler;
use App\Support\Filesystem\Filers\Filer;

abstract class CsvExport extends Export
{
    protected bool $continuousData = false;

    protected function filer(): Filer
    {
        return CsvFiler::create();
    }

    /**
     * @throws FileException
     */
    protected function exportBefore(Filer $filer)
    {
        $filer->openForWriting();
    }

    protected function exportAfter(Filer $filer)
    {
        $filer->close();
        parent::exportAfter($filer);
    }

    protected function export(Filer $filer)
    {
        if ($headers = $this->headers()) {
            $filer->write($headers);
        }
        if ($this->continuousData) {
            while ($data = $this->data()) {
                $filer->writeAll($data, false);
            }
            return;
        }
        if ($data = $this->data()) {
            $filer->writeAll($data, false);
        }
    }

    protected function headers(): ?array
    {
        return null;
    }

    protected function data(): ?array
    {
        return null;
    }
}