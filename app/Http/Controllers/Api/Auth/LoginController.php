<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\UserProvider;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Http\Controllers\ModelApiController;
use App\Support\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

/**
 * @method UserProvider modelProvider()
 */
class LoginController extends ModelApiController
{
    protected string $modelProviderClass = UserProvider::class;

    protected function storeRules(Request $request): array
    {
        return [
            'email' => 'required',
            'password' => 'required',
        ];
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     * @throws AuthenticationException
     */
    protected function storeExecute(Request $request)
    {
        $user = $this->modelProvider()->firstByEmail($request->input('email'));
        if (!Hash::check($request->input('password'), $user->getAuthPassword())) {
            throw new AuthenticationException();
        }
        $token = $user->createToken('login');
        return $this->response($request, $token);
    }
}
