<?php

namespace App\Http\Controllers\Api\Trial;

use App\Http\Resources\UserResource;
use App\Models\UserProvider;
use App\Support\Client\DateTimer;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Facades\Client;
use App\Support\Http\Controllers\ModelApiController;
use App\Support\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends ModelApiController
{
    protected string $modelProviderClass = UserProvider::class;

    protected string $modelResourceClass = UserResource::class;

    protected function conditionParams(Request $request): array
    {
        $dateTimer = Client::dateTimer();
        return [
            'email',
            'name',
            'created_from' => function ($input) use ($dateTimer) {
                try {
                    return $dateTimer->fromFormatToDatabaseFormat(
                        $dateTimer->compoundFormat('shortDate', ' ', 'shortTime'),
                        $input,
                        DateTimer::DAY_TYPE_MINUTE_START
                    );
                }
                finally {
                    return null;
                }
            },
            'created_to' => function ($input) use ($dateTimer) {
                try {
                    return $dateTimer->fromFormatToDatabaseFormat(
                        $dateTimer->compoundFormat('shortDate', ' ', 'shortTime'),
                        $input,
                        DateTimer::DAY_TYPE_MINUTE_END
                    );
                }
                finally {
                    return null;
                }
            },
        ];
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

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function storeExecute(Request $request)
    {
        return $this->modelProvider()->createWithAttributes([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'email_verified_at' => DateTimer::databaseNow(),
        ]);
    }
}
