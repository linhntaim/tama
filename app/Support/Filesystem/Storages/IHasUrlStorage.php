<?php

namespace App\Support\Filesystem\Storages;

interface IHasUrlStorage
{
    public function setUrl(string $url): static;

    public function getUrl(): string;
}
