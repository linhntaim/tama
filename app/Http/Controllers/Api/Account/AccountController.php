<?php

namespace App\Http\Controllers\Api\Account;

use App\Models\UserProvider;
use App\Support\Http\Controllers\ModelApiController;
use Illuminate\Http\Request;

/**
 * @method UserProvider modelProvider()
 */
class AccountController extends ModelApiController
{
    protected string $modelProviderClass = UserProvider::class;

    protected function current(Request $request)
    {
        return $this->showResponse($request, $request->user());
    }
}
