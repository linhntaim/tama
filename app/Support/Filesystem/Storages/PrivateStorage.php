<?php

namespace App\Support\Filesystem\Storages;

class PrivateStorage extends LocalStorage
{
    public const NAME = 'private';

    public function __construct()
    {
        parent::__construct('private');
    }
}