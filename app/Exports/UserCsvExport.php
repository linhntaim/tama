<?php

namespace App\Exports;

use App\Http\Resources\UserExportResource;
use App\Models\UserProvider;
use App\Support\Exports\ModelCsvExport;

class UserCsvExport extends ModelCsvExport
{
    public const NAME = 'users';

    protected function headers(): array
    {
        return [
            'ID',
            'Name',
            'Email',
        ];
    }

    protected function modelProviderClass(): string
    {
        return UserProvider::class;
    }

    protected function modelResourceClass(): string
    {
        return UserExportResource::class;
    }
}
