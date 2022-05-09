<?php

namespace App\Http\Controllers\Api\Account;

use App\Models\UserProvider;
use App\Support\Http\Controllers\ModelApiController;
use App\Support\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @method UserProvider modelProvider()
 */
class AccountController extends ModelApiController
{
    protected string $modelProviderClass = UserProvider::class;

    public function show(Request $request): JsonResponse
    {
        return $this->responseModel($request, $request->user());
    }
}
