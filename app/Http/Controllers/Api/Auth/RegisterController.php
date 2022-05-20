<?php

namespace App\Http\Controllers\Api\Auth;

use App\Events\Registered;
use App\Models\UserProvider;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Http\Controllers\ModelApiController;
use App\Support\Http\Request;
use App\Support\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * @method UserProvider modelProvider()
 */
class RegisterController extends ModelApiController
{
    protected string $modelProviderClass = UserProvider::class;

    protected function registerRules(Request $request): array
    {
        return [
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
            ],
            'password' => 'required|min:8',
        ];
    }

    /**
     * @throws ValidationException
     */
    protected function registerValidate(Request $request)
    {
        $this->validate($request, $this->registerRules($request));
    }

    /**
     * @throws ValidationException
     * @throws DatabaseException
     * @throws Exception
     */
    public function register(Request $request): JsonResponse
    {
        $this->registerValidate($request);

        $this->transactionStart();
        return $this->registerResponse($request, $this->registerExecute($request));
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function registerExecute(Request $request): User
    {
        return $this->modelProvider()->createWithAttributes([
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ]);
    }

    protected function registerEvent(Request $request, User $user)
    {
        Registered::dispatch($user, $request->input('password'));
    }

    protected function registerResponse(Request $request, User $user): JsonResponse
    {
        $this->registerEvent($request, $user);

        $this->transactionComplete();

        return $this->responseSuccess($request);
    }
}
