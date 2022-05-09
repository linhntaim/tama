<?php

namespace App\Support\Filesystem\Storages;

trait HasUrlStorage
{
    public function setUrl(string $url): static
    {
        return $this->setFile($url);
    }
}