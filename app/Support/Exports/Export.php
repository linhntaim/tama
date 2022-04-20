<?php

namespace App\Support\Exports;

use App\Support\Filesystem\Filers\Filer;

abstract class Export
{
    protected abstract function filer(): Filer;

    protected function exportBefore(Filer $filer)
    {
    }

    protected function exportAfter(Filer $filer)
    {
        $filer->publishPrivate();
    }

    protected abstract function export(Filer $filer);

    public function __invoke(): Filer
    {
        $filer = $this->filer();
        $this->exportBefore($filer);
        $this->export($filer);
        $this->exportAfter($filer);
        return $filer;
    }
}