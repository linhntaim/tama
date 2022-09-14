<?php

namespace App\Console\Commands\Trial;

use App\Imports\BatchUserCsvImport;
use App\Support\Console\Commands\ImportCommand;

class UsersImportCommand extends ImportCommand
{
    protected function importClass(): string
    {
        return BatchUserCsvImport::class;
    }
}
