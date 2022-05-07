<?php

namespace App\Console\Commands\Trial;

use App\Exports\UserCsvExport;
use App\Support\Console\Commands\ExportCommand;

class UsersExportCommand extends ExportCommand
{
    protected function exportClass(): string
    {
        return UserCsvExport::class;
    }
}
