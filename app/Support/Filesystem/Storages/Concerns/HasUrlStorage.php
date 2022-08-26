<?php

namespace App\Support\Filesystem\Storages\Concerns;

trait HasUrlStorage
{
    public function setUrl(string $url): static
    {
        return $this->setFile($url);
    }
}
