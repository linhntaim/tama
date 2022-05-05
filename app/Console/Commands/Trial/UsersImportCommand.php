<?php

namespace App\Console\Commands\Trial;

use App\Imports\TrialBatchUserCsvImport;
use App\Support\Console\Commands\ImportCommand;

class UsersImportCommand extends ImportCommand
{
    protected function importClass(): string
    {
        return TrialBatchUserCsvImport::class;
    }
}
