<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Http\File;
use BadMethodCallException;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use SplFileInfo;

/**
 * @property File|UploadedFile|null $file
 */
class InternalStorage extends Storage implements IDirectEditableStorage
{
    public const NAME = 'internal';

    public function fromFile(string|SplFileInfo|Storage $file): static
    {
        throw new BadMethodCallException('Internal Storage does not support `fromFile` method.');
    }

    public function setFile(string|SplFileInfo $file): static
    {
        $file = File::from($file, false);
        if ($file->isFile()) {
            if ($file instanceof UploadedFile) {
                $this
                    ->setName($file->getClientOriginalName())
                    ->setMimeType($file->getClientMimeType())
                    ->setExtension($file->getClientOriginalExtension())
                    ->setSize($file->getSize());
            }
            elseif ($file instanceof File) {
                $this
                    ->setName($file->getBasename())
                    ->setMimeType($file->getMimeType())
                    ->setExtension($file->extension())
                    ->setSize($file->getSize());
            }
            return parent::setFile($file);
        }
        return $this;
    }

    public function getContent(): string
    {
        return $this->file->getContent();
    }

    public function getStream()
    {
        if (($f = fopen($path = $this->getRealPath(), 'r')) === false) {
            throw new RuntimeException(sprintf('Cannot get stream from the file [%s]', $path));
        }
        return $f;
    }

    public function delete(): static
    {
        unlink($this->getRealPath());
        return $this;
    }

    public function getRealPath(): string
    {
        return $this->file->getRealPath();
    }
}