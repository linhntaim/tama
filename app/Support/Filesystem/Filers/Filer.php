<?php

namespace App\Support\Filesystem\Filers;

use App\Support\Filesystem\Storages\AwsS3Storage;
use App\Support\Filesystem\Storages\AzureBlobStorage;
use App\Support\Filesystem\Storages\ExternalStorage;
use App\Support\Filesystem\Storages\InlineStorage;
use App\Support\Filesystem\Storages\InternalStorage;
use App\Support\Filesystem\Storages\PrivateStorage;
use App\Support\Filesystem\Storages\PublicStorage;
use App\Support\Filesystem\Storages\Storage;
use App\Support\Filesystem\Storages\StorageFactory;
use Illuminate\Http\UploadedFile;
use SplFileInfo;

class Filer
{
    public static function from(string|SplFileInfo|Storage $file): ?static
    {
        if ($file instanceof UploadedFile) {
            return take(new static(), function (Filer $filer) use ($file) {
                $filer->storage = (new PrivateStorage())->fromFile($file);
            });
        }
        foreach (is_url($file) ? [
            PublicStorage::class,
            config_starter('filesystems.uses.s3') ? AwsS3Storage::class : null,
            config_starter('filesystems.uses.azure') ? AzureBlobStorage::class : null,
            ExternalStorage::class,
        ] : [
            InlineStorage::class,
            PublicStorage::class,
            PrivateStorage::class,
            InternalStorage::class,
        ] as $storageClass) {
            if (!is_null($storageClass) && ($storage = new $storageClass())->setFile($file)->has()) {
                return take(new static(), function (Filer $filer) use ($storage) {
                    $filer->storage = $storage;
                });
            }
        }
        return null;
    }

    protected Storage $storage;

    private function __construct()
    {
    }

    public function getName(): string
    {
        return $this->storage->getName();
    }

    public function getMimeType(): string
    {
        return $this->storage->getMimeType();
    }

    public function getExtension(): string
    {
        return $this->storage->getExtension();
    }

    public function getSize(): string
    {
        return $this->storage->getSize();
    }

    public function getVisibility(): string
    {
        return $this->storage->getVisibility();
    }

    protected function moveToStorage(Storage $toStorage, ?string $in = null): static
    {
        $storage = $this->storage;
        $this->storage = $toStorage->fromFile($this->storage, $in);
        $storage->delete();
        return $this;
    }

    public function storeLocally(?string $in = null): static
    {
        return $this->moveToStorage(StorageFactory::localStorage()->setVisibility('private'), $in);
    }

    public function publishPrivate(?string $in = null): static
    {
        return $this->moveToStorage(StorageFactory::privatePublishStorage()->setVisibility('private'), $in);
    }

    public function publishPublic(?string $in = null): static
    {
        return $this->moveToStorage(StorageFactory::publicPublishStorage()->setVisibility('public'), $in);
    }
}