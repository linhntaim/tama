<?php

namespace App\Support\Filesystem\Storages;

use Illuminate\Contracts\Filesystem\Filesystem;
use SplFileInfo;

abstract class Storage
{
    public const NAME = 'storage';

    protected mixed $file = null;

    protected string $name;

    protected string $mimeType;

    protected string $extension;

    protected int $size;

    protected string $visibility = Filesystem::VISIBILITY_PRIVATE;

    public abstract function fromFile(string|SplFileInfo|Storage $file): static;

    public function setFile(string|SplFileInfo $file): static
    {
        $this->file = $file;
        return $this;
    }

    public function getFile(): mixed
    {
        return $this->file;
    }

    public abstract function getContent(): string;

    /**
     * @return resource
     */
    public abstract function getStream();

    public function has(): bool
    {
        return !is_null($this->file);
    }

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

    public function setExtension(string $extension): static
    {
        $this->extension = $extension;
        return $this;
    }

    public function getExtension(): string
    {
        return $this->extension;
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

    public function setVisibility(string $visibility): static
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function delete(): static
    {
        return $this;
    }
}