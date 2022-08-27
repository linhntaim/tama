<?php

namespace App\Support\Http\Controllers\Api\Auth;

use App\Support\Http\Controllers\ApiController;
use App\Support\Http\Resources\Concerns\ResourceTransformer;
use App\Support\Http\Resources\ModelResource;
use App\Support\Http\Resources\Resource;
use App\Support\Models\Contracts\HasApiTokens as HasApiTokensContract;
use App\Support\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Pipeline;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Http\Requests\LoginRequest;

abstract class AuthenticatedTokenController extends ApiController
{
    use ResourceTransformer;

    protected string $modelResourceClass = ModelResource::class;

    protected string $tokenResourceClass = Resource::class;

    #region Login
    public function store(LoginRequest $request)
    {
        return $this->loginPipeline($request)->then(fn() => $this->loginResponse($request));
    }

    protected function loginPipeline(LoginRequest $request): \Illuminate\Pipeline\Pipeline
    {
        return (new Pipeline(app()))->send($request)->through(array_filter($this->loginPipes()));
    }

    abstract protected function loginPipes(): array;

    protected function loginUser(LoginRequest $request): User|HasApiTokensContract
    {
        return $request->user();
    }

    protected function loginUserTransform(LoginRequest $request, User|HasApiTokensContract $user): array
    {
        return $this->resourceTransform($user, $this->modelResourceClass, $request);
    }

    protected function loginTokenTransform(LoginRequest $request, User|HasApiTokensContract $user): array
    {
        return $this->resourceTransform($user->retrieveToken(), $this->tokenResourceClass, $request);
    }

    protected function loginResponse(LoginRequest $request)
    {
        return app(LoginResponseContract::class)->setJsonDataCallback(
            function ($data) use ($request) {
                $user = $this->loginUser($request);
                return array_merge(
                    $data,
                    [
                        'model' => $this->loginUserTransform($request, $user),
                        'token' => $this->loginTokenTransform($request, $user),
                    ]
                );
            }
        );
    }
    #endregion

    #region Logout
    abstract protected function destroyAllTokens(Request $request): void;

    abstract protected function destroyToken(Request $request, $token): void;

    abstract protected function currentToken(Request $request): mixed;

    protected function tokenForDestroying(Request $request, $currentToken)
    {
        return $request->input('id', $currentToken);
    }

    public function destroy(Request $request): LogoutResponse
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
        return app(LogoutResponse::class);
    }
    #endregion
}
