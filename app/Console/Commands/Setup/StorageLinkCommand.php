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
        if ($this->call('storage:link', [
                '--force' => $this->forced(),
            ]) == self::SUCCESS) {
            foreach ($this->links() as $link => $target) {
                if (!file_exists($link)) {
                    if (false === mkdir_recursive($link)) {
                        $this->error(sprintf('Cannot create [%s] link.', $link));
                        return $this->exitFailure();
                    }

                    copy_recursive($target, $link);
                }
            }
        }
        return $this->exitSuccess();
    }
}
