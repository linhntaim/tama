<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\UserProvider;
use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class AuthenticateWithCredentials
{
    protected function guard(): string
    {
        return config('fortify.guard');
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     * @throws ValidationException
     */
    public function handle(Request $request, $next)
    {
        if (!($user = $this->attempt($request))) {
            $this->fireFailedEvent($request);
            $this->throwFailedAuthenticationException($request);
        }

        $this->setAuthUser($request, $user);

        return $next($request);
    }

    protected function setAuthUser(Request $request, User $user)
    {
        auth($this->guard())->setUser($user);
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function retrieveUser(Request $request): ?User
    {
        return (new UserProvider())
            ->notStrict()
            ->firstByUsername(Fortify::username(), $request->input(Fortify::username()));
    }

    protected function matchPassword(Request $request, User $user): bool
    {
        return $user->matchPassword($request->input('password'));
    }

    /**
     * @throws DatabaseException
     * @throws Exception
     */
    protected function attempt(Request $request): ?User
    {
        $user = $this->retrieveUser($request);
        return $user && $this->matchPassword($request, $user) ? $user : null;
    }

    /**
     * @throws ValidationException
     */
    protected function throwFailedAuthenticationException(Request $request)
    {
        throw ValidationException::withMessages([
            Fortify::username() => [trans('auth.failed')],
        ]);
    }

    protected function fireFailedEvent(Request $request)
    {
        event(new Failed($this->guard(), null, [
            Fortify::username() => $request->input(Fortify::username()),
            'password' => $request->input('password'),
        ]));
    }
}
