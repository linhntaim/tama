<?php

namespace App\Support\Filesystem\Storages;

class AwsS3Storage extends CloudStorage
{
    public const NAME = 's3';

    public function __construct()
    {
        parent::__construct('s3');
    }
}