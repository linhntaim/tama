<?php

namespace App\Support\Filesystem\Storages;

class AzureBlobStorage extends CloudStorage
{
    public const NAME = 'azure';

    public function __construct()
    {
        parent::__construct('azure');
    }
}