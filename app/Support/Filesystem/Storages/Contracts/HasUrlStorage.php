<?php

namespace App\Support\Filesystem\Storages\Contracts;

interface HasUrlStorage
{
    public function setUrl(string $url): static;

    public function getUrl(): string;
}
