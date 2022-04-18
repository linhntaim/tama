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
use Illuminate\Support\Facades\Validator;

class Filer
{
    public static function from($source): ?static
    {
        if (is_string($source)) {
            if (($storage = new InlineStorage())->setData($source)->has()) {
                return take(new static(), function (Filer $filer) use ($storage) {
                    $filer->storage = $storage;
                });
            }
            if (!Validator::make(['source' => $source], ['source' => 'url'])->fails()) {
                foreach ([
                             PublicStorage::class,
                             config_starter('filesystems.s3') ? AwsS3Storage::class : null,
                             config_starter('filesystems.azure') ? AzureBlobStorage::class : null,
                             ExternalStorage::class,
                         ] as $storageClass) {
                    if (!is_null($storageClass)
                        && ($storage = new $storageClass())->setUrl($source)->has()) {
                        return take(new static(), function (Filer $filer) use ($storage) {
                            $filer->storage = $storage;
                        });
                    }
                }
                return null;
            }
        }
        if ($source instanceof UploadedFile) {
            return take(new static(), function (Filer $filer) use ($source) {
                $filer->storage = (new PrivateStorage())->from($source);
            });
        }
        foreach ([
                     PublicStorage::class,
                     PrivateStorage::class,
                     InternalStorage::class,
                 ] as $storageClass) {
            if (($storage = new $storageClass())->setFile($source)->has()) {
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

    public function moveToStorage(Storage $toStorage): static
    {
        if ($toStorage::class != $this->storage::class) {
            $this->storage = $toStorage->from($this->storage);
        }
        return $this;
    }

    public function moveToPublic(): static
    {
        return $this->moveToStorage(new PublicStorage());
    }

    public function moveToPrivate(): static
    {
        return $this->moveToStorage(new PrivateStorage());
    }

    public function moveToLocal(): static
    {
        return $this->moveToStorage(StorageFactory::localStorage());
    }

    public function moveToCloud(): static
    {
        return $this->moveToStorage(StorageFactory::cloudStorage());
    }
}