<?php

namespace App\Support\Filesystem\Storages;

use Illuminate\Support\Str;

trait HasPublicStorage
{
    protected string $rootUrl;

    /**
     * @return string
     */
    public function getRootUrl(): string
    {
        return $this->rootUrl ?? $this->rootUrl = Str::beforeLast($this->getDisk()->url('a'), '/a');
    }

    public function setUrl(string $url): static
    {
        if (Str::startsWith($url, $rootUrl = $this->getRootUrl() . '/')) {
            return $this->setRelativePath(str_replace($rootUrl, '', $url), false)
                ->setName(basename($this->getRelativePath()))
                ->setMimeType($this->disk->mimeType($this->getRelativePath()))
                ->setSize($this->disk->size($this->getRelativePath()));
        }
        return $this;
    }

    public function getUrl(): string
    {
        return $this->disk->url($this->relativePath);
    }
}