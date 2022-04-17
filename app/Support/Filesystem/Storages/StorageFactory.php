<?php

namespace App\Support\Filesystem\Storages;

class StorageFactory
{
    public static function cloudStorage(): ?CloudStorage
    {
        if ($disk = config('filesystems.cloud')) {
            if ($disk == 's3') {
                if (config_starter('filesystems.s3')) {
                    return new AwsS3Storage();
                }
            }
            elseif ($disk == 'azure') {
                if (config_starter('filesystems.azure')) {
                    return new AzureBlobStorage();
                }
            }
            return new CloudStorage();
        }
        return null;
    }

    public static function localStorage(): ?LocalStorage
    {
        if ($disk = config('filesystems.disk')) {
            if ($disk == 'private') {
                return new PrivateStorage();
            }
            elseif ($disk == 'public') {
                return new PublicStorage();
            }
            return new LocalStorage();
        }
        return null;
    }

    public static function create($name): ?Storage
    {
        return match ($name) {
            's3' => new AwsS3Storage(),
            'azure' => new AzureBlobStorage(),
            'cloud' => new CloudStorage(),
            'public' => new PublicStorage(),
            'private' => new PrivateStorage(),
            'local' => new LocalStorage(),
            'internal' => new InternalStorage(),
            'external' => new ExternalStorage(),
            'inline' => new InlineStorage(),
            default => null,
        };
    }
}