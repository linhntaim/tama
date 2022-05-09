<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\UserProvider;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Http\Controllers\ModelApiController;
use App\Support\Http\Request;
use App\Support\Http\Resources\ResourceTransformer;
use App\Support\Http\Resources\SanctumAccessTokenResource;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @method UserProvider modelProvider()
 */
class LoginController extends ModelApiController
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
     * @throws AuthenticationException
     */
    protected function loginExecute(Request $request): array
    {
        $user = $this->modelProvider()
            ->notStrict()
            ->firstByEmail($request->input('email'));
        if (!$user || !Hash::check($request->input('password'), $user->getAuthPassword())) {
            throw new AuthenticationException();
        }
        return [$user, $user->createToken('login')];
    }

    /**
     * @param Request $request
     * @param $user
     * @param $token
     * @return JsonResponse
     */
    protected function loginResponse(Request $request, $user, $token): JsonResponse
    {
        $this->transactionComplete();

        return $this->responseModel(
            $request,
            $user,
            $this->modelResourceClass,
            $this->tokenTransform($request, $token)
        );
    }

    protected function tokenTransform(Request $request, $token): array
    {
        return $this->resourceTransform($token, SanctumAccessTokenResource::class, $request, 'token');
    }
}
