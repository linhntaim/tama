<?php

/**
 * Base
 */

namespace App\Console\Commands\Setup;

use App\Support\Console\Commands\ForceCommand;

class StorageLinkCommand extends ForceCommand
{
    protected function links(): array
    {
        return $this->laravel['config']['filesystems.links'] ??
            [public_path('storage') => storage_path('app/public')];
    }

    protected function handling(): int
    {
        return $this->call('storage:link');
    }
}
