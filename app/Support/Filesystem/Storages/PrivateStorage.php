<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Filesystem\Storages\Contracts\PrivatePublishableStorage as PrivatePublishableStorageContract;

class PrivateStorage extends LocalStorage implements PrivatePublishableStorageContract
{
    public const NAME = 'private';

    public function __construct()
    {
        parent::__construct('private');
    }
}
