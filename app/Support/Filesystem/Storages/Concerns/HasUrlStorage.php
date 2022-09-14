<?php

namespace App\Support\Filesystem\Storages\Concerns;

use App\Support\Exceptions\FileNotFoundException;

trait HasUrlStorage
{
    /**
     * @throws FileNotFoundException
     */
    public function setUrl(string $url): static
    {
        return $this->setFile($url);
    }
}
