<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Http\File;
use BadMethodCallException;
use Illuminate\Http\UploadedFile;

class InlineStorage extends Storage
{
    public const NAME = 'inline';

    protected ?string $data = null;

    public function getContent(): string
    {
        return base64_decode($this->data);
    }

    public function getContentAsStream()
    {
        throw new BadMethodCallException('Inline Storage does not support `getContentAsStream` method.');
    }

    public function has(): bool
    {
        return !is_null($this->data);
    }

    public function from(string|UploadedFile|File|Storage $file): static
    {
        if (!$file instanceof Storage) {
            $file = File::from($file);
        }
        return $this->setData(base64_encode($file->getContent()), false);
    }

    public function setData(string $data, bool $check = true): static
    {
        $this->data = $check ? (is_base64($data) ? $data : null) : $data;
        return $this;
    }
}