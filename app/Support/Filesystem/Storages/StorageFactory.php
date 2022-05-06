<?php

namespace App\Support\Filesystem\Storages;

use RuntimeException;

class StorageFactory
{
    public static function privatePublishStorage(): Storage|IPrivatePublishableStorage
    {
        return take(static::create(config_starter('filesystems.storages.publish.private')), function (?Storage $storage) {
            if (is_null($storage)) {
                throw new RuntimeException('Private publish storage was not set');
            }
            if (!($storage instanceof IPublishableStorage)) {
                throw new RuntimeException(sprintf('Storage [%s] is not a publishable storage', $storage::class));
            }
            $storage->setVisibility('private');
        });
    }

    public static function publicPublishStorage(): Storage|IPublicPublishableStorage
    {
        return take(static::create(config_starter('filesystems.storages.publish.public')), function (?Storage $storage) {
            if (is_null($storage)) {
                throw new RuntimeException('Public publish storage was not set');
            }
            if (!($storage instanceof IPublicPublishableStorage)) {
                throw new RuntimeException(sprintf('Storage [%s] is not a publishable storage', $storage::class));
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
