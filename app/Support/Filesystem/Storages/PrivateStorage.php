<?php

namespace App\Support\Filesystem\Storages;

class PrivateStorage extends LocalStorage implements IPrivatePublishableStorage
{
    public const NAME = 'private';

    public function __construct()
    {
        parent::__construct('private');
    }
}
