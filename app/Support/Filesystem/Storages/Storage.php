<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Http\File;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;

abstract class Storage
{
    public const NAME = 'storage';

    protected string $name;

    protected string $mimeType;

    protected int $size;

    protected string $visibility = Filesystem::VISIBILITY_PRIVATE;

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getExtension(): string
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }

    public function setVisibility(string $visibility): static
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public abstract function getContent(): string;

    /**
     * @return resource
     */
    public abstract function getContentAsStream();

    public abstract function has(): bool;

    public abstract function from(string|UploadedFile|File|Storage $file): static;
}