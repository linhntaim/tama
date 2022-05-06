<?php

namespace App\Imports\Trial;

use App\Models\User;
use App\Models\UserProvider;
use App\Support\Client\DateTimer;
use App\Support\Exports\Export;
use App\Support\Exports\SimpleCsvExport;
use App\Support\Imports\BatchModelCsvImport;

class BatchUserCsvImport extends BatchModelCsvImport
{
    public const NAME = 'trial_users';

    public static function sample(): Export
    {
        return new SimpleCsvExport(
            [
                ['A', 'a@a.com', '12345678'],
                ['B', 'b@b.com', '12345678'],
                ['C', 'c@c.com', '12345678'],
            ],
            ['Name', 'Email', 'Password'],
        );
    }

    protected bool $hasHeaders = true;

    protected array $attributeKeyMap = [
        'name',
        'email',
        'password',
    ];

    protected function modelProviderClass(): string
    {
        return UserProvider::class;
    }

    protected function dataValidationRules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
        ];
    }

    protected function dataMap(array $data): array
    {
        $now = DateTimer::databaseNow();
        return parent::dataMap($data)
            + [
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
    }

    protected function dataImport(array $data)
    {
        $data['password'] = User::hashPassword($data['password']);
        parent::dataImport($data);
    }
}
