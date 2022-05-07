<?php

namespace App\Support\Filesystem\Storages;

use Illuminate\Support\Str;

trait HasUrlDiskStorage
{
    use HasUrlStorage;

    protected string $rootUrl;

    /**
     * @return string
     */
    public function getRootUrl(): string
    {
        return $this->rootUrl ?? $this->rootUrl = Str::beforeLast($this->disk->url('a'), '/a');
    }

    public function getUrl(): string
    {
        return $this->disk->url(concat_urls($this->file));
    }
}
