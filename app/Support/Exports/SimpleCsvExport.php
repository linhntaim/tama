<?php

namespace App\Support\Exports;

class SimpleCsvExport extends CsvExport
{
    public function __construct(array $data = [], ?array $headers = null)
    {
        $this->data = $data;
        $this->headers = $headers;
    }

    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;
        return $this;
    }

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }
}
