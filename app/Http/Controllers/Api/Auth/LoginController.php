<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\UserProvider;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Http\Controllers\ModelApiController;
use App\Support\Http\Request;
use App\Support\Http\Resources\ResourceTransformer;
use App\Support\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @method UserProvider modelProvider()
 */
abstract class LoginController extends ModelApiController
{
    use ResourceTransformer;

    protected string $modelProviderClass = UserProvider::class;

    protected function loginRules(Request $request): array
    {
        return [
            'email' => 'required',
            'password' => 'required',
        ];
    }

    /**
     * @throws ValidationException
     */
    protected function loginValidate(Request $request)
    {
        $this->validate($request, $this->loginRules($request));
    }

    /**
     * @throws ValidationException
     * @throws DatabaseException
     * @throws Exception
     * @throws AuthenticationException
     */
    public function login(Request $request): JsonResponse
    {
        $this->loginValidate($request);

        $this->transactionStart();
        return $this->loginResponse($request, ...$this->loginExecute($request));
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function loginFindUser(Request $request): ?User
    {
        return $this->modelProvider()
            ->notStrict()
            ->firstByEmail($request->input('email'));
    }

    protected function loginMatchPassword(Request $request, User $user): bool
    {
        return $user->matchPassword($request->input('password'));
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     * @throws AuthenticationException
     */
    protected function loginUser(Request $request): User
    {
        $user = $this->loginFindUser($request);
        if (!$user || !$this->loginMatchPassword($request, $user)) {
            throw new AuthenticationException();
        }
        return $user;
    }

    protected abstract function loginToken(Request $request, User $user): mixed;

    /**
     * @throws DatabaseException
     * @throws Exception
     * @throws AuthenticationException
     */
    protected function loginExecute(Request $request): array
    {
        return [$user = $this->loginUser($request), $this->loginToken($request, $user)];
    }

    protected function loginResponse(Request $request, User $user, $token): JsonResponse
    {
        $this->transactionComplete();

        return $this->responseModel(
            $request,
            $user,
            $this->modelResourceClass,
            $this->loginTokenTransform($request, $token)
        );
    }

    protected abstract function loginTokenTransform(Request $request, $token): array;
}
