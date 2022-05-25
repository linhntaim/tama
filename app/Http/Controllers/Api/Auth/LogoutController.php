<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\UserProvider;
use App\Support\Http\Controllers\ModelApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @method UserProvider modelProvider()
 */
abstract class LogoutController extends ModelApiController
{
    protected string $modelProviderClass = UserProvider::class;

    public abstract function destroyAllTokens(Request $request);

    public abstract function destroyToken(Request $request, $token);

    protected abstract function currentToken(Request $request): mixed;

    protected function tokenForDestroying(Request $request, $currentToken)
    {
        return $request->input('id', $currentToken);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->has('all')) {
            $this->destroyAllTokens($request);
        }
        else {
            $this->destroyToken(
                $request,
                $this->tokenForDestroying(
                    $request,
                    $this->currentToken($request)
                )
            );
        }
        return $this->responseSuccess($request);
    }
}
