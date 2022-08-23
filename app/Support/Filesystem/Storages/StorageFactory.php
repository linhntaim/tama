<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Filesystem\Storages\Contracts\PrivatePublishableStorage as PrivatePublishableStorageContract;
use App\Support\Filesystem\Storages\Contracts\PublicPublishableStorage as PublicPublishableStorageContract;
use RuntimeException;

class StorageFactory
{
    public static function privatePublishStorage(): Storage|PrivatePublishableStorageContract
    {
        return take(static::create(config_starter('filesystems.storages.publish.private')), function (?Storage $storage) {
            if (is_null($storage)) {
                throw new RuntimeException('Private publish storage was not set');
            }
            if (!($storage instanceof PrivatePublishableStorageContract)) {
                throw new RuntimeException(sprintf('Storage [%s] is not a private publishable storage', $storage::class));
            }
            $storage->setVisibility('private');
        });
    }

    public static function publicPublishStorage(): Storage|PublicPublishableStorageContract
    {
        return take(static::create(config_starter('filesystems.storages.publish.public')), function (?Storage $storage) {
            if (is_null($storage)) {
                throw new RuntimeException('Public publish storage was not set');
            }
            if (!($storage instanceof PublicPublishableStorageContract)) {
                throw new RuntimeException(sprintf('Storage [%s] is not a public publishable storage', $storage::class));
            }
            $storage->setVisibility('public');
        });
    }

    public static function localStorage(): LocalStorage
    {
        return take(static::create(config_starter('filesystems.storages.local')), function (?Storage $storage) {
            if (is_null($storage)) {
                throw new RuntimeException('Local storage was not set');
            }
            if (!($storage instanceof LocalStorage)) {
                throw new RuntimeException(sprintf('Storage [%s] is not a local storage', $storage::class));
            }
            $storage->setVisibility('private');
        });
    }

    public static function create($name): ?Storage
    {
        return match ($name) {
            's3' => new AwsS3Storage(),
            'azure' => new AzureBlobStorage(),
            //'cloud' => new CloudStorage(),
            'public' => new PublicStorage(),
            'private' => new PrivateStorage(),
            //'local' => new LocalStorage(),
            'internal' => new InternalStorage(),
            'external' => new ExternalStorage(),
            'inline' => new InlineStorage(),
            default => null,
        };
    }
}
