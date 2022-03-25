<?php

/**
 * Base
 */

namespace App\Console\Commands\Setup;

use App\Support\Console\Commands\ForceCommand;

class KeyGenerateCommand extends ForceCommand
{
    protected function handling(): int
    {
        return $this->call('key:generate');
    }
}
