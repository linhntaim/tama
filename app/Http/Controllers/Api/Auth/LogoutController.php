<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\UserProvider;
use App\Support\Http\Controllers\ModelApiController;
use App\Support\Http\Request;

/**
 * @method UserProvider modelProvider()
 */
class LogoutController extends ModelApiController
{
    protected string $modelProviderClass = UserProvider::class;

    public function logout(Request $request)
    {
        if ($request->has('all')) {
            $request->user()->tokens()->delete();
        }
        else {
            $request->user()->tokens()
                ->where('id', $request->input('id', $request->user()->currentAccessToken()->id))
                ->delete();
        }
        return $this->responseSuccess($request);
    }
}
