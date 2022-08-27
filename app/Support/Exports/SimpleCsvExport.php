<?php

namespace App\Support\Exports;

class SimpleCsvExport extends CsvExport
{
    /**
     * @param array[] $data
     * @param string[]|null $headers
     */
    public function __construct(array $data = [], ?array $headers = null)
    {
        $this->data = $data;
        $this->headers = $headers;
    }

    /**
     * @param string[] $headers
     * @return $this
     */
    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param array[] $data
     * @return $this
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }
}
