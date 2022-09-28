<?php

namespace App\Http\Controllers\Api\Trial;

use App\Exports\UserCsvExport;
use App\Http\Resources\UserResource;
use App\Imports\UserCsvImport;
use App\Models\UserProvider;
use App\Support\Client\DateTimer;
use App\Support\Facades\Client;
use App\Support\Http\Controllers\ModelApiController;
use App\Support\Models\QueryValues\LikeValue;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends ModelApiController
{
    protected string $modelProviderClass = UserProvider::class;

    protected string $modelResourceClass = UserResource::class;

    protected function conditionParams(Request $request): array
    {
        $dateTimer = Client::dateTimer();
        return [
            'email' => fn($input) => new LikeValue($input),
            'name' => fn($input) => new LikeValue($input),
            'created_from' => function ($input) use ($dateTimer) {
                return $dateTimer->fromFormatToDatabaseFormat(
                    $dateTimer->compoundFormat('shortDate', ' ', 'shortTime'),
                    $input,
                    DateTimer::DAY_TYPE_MINUTE_START
                );
            },
            'created_to' => function ($input) use ($dateTimer) {
                return $dateTimer->fromFormatToDatabaseFormat(
                    $dateTimer->compoundFormat('shortDate', ' ', 'shortTime'),
                    $input,
                    DateTimer::DAY_TYPE_MINUTE_END
                );
            },
        ];
    }

    protected function exporterClass(Request $request): ?string
    {
        return UserCsvExport::class;
    }

    protected function importerClass(Request $request): ?string
    {
        return UserCsvImport::class;
    }

    protected function storeRules(Request $request): array
    {
        return [
            'name' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
            ],
            'password' => 'required|string|min:8',
        ];
    }

    protected function storeExecute(Request $request)
    {
        return $this->modelProvider()->createWithAttributes([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'email_verified_at' => DateTimer::databaseNow(),
        ]);
    }

    protected function updateRules(Request $request): array
    {
        return [
            'name' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->ignoreModel($this->modelProvider()->current()),
            ],
            'password' => 'required|string|min:8',
        ];
    }

    protected function updateExecute(Request $request)
    {
        return $this->modelProvider()->updateWithAttributes([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);
    }
}
