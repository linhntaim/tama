<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Exceptions\FileNotFoundException;
use App\Support\Filesystem\Storages\Contracts\PrivatePublishableStorage as PrivatePublishableStorageContract;
use App\Support\Filesystem\Storages\Contracts\PublicPublishableStorage as PublicPublishableStorageContract;
use App\Support\Http\File;
use BadMethodCallException;
use Illuminate\Http\UploadedFile;
use SplFileInfo;

class InlineStorage extends Storage implements PrivatePublishableStorageContract,
                                               PublicPublishableStorageContract
{
    public const NAME = 'inline';

    /**
     * @throws FileNotFoundException
     */
    public function fromFile(string|SplFileInfo|Storage $file): static
    {
        if ($file instanceof Storage) {
            return parent::setFile(base64_encode($file->getContent()))
                ->setName($file->getName())
                ->setMimeType($file->getMimeType())
                ->setExtension($file->getExtension())
                ->setSize($file->getSize());
        }
        $file = File::from($file);
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
        return parent::setFile(base64_encode($file->getContent()));
    }

    public function setFile(SplFileInfo|string $file): static
    {
        if (is_base64($file)) {
            return parent::setFile($file)
                ->setName('blob')
                ->setMimeType('')
                ->setExtension('')
                ->setSize(0);
        }
        return $this;
    }

    public function getContent(): string
    {
        return base64_decode($this->file);
    }

    public function getStream()
    {
        throw new BadMethodCallException('Inline Storage does not support `getStream` method.');
    }

    public function setData($data): static
    {
        if (is_base64($data)) {
            return parent::setFile($data)
                ->setName('blob')
                ->setMimeType('')
                ->setExtension('')
                ->setSize(0);
        }
        return $this;
    }
}
