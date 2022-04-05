<?php

/**
 * Base
 */

namespace App\Exceptions;

use App\Support\Console\Artisan;
use App\Support\Http\Request;
use App\Support\Http\ResponseTrait;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    use ResponseTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * @throws BindingResolutionException
     */
    protected function request(): Request
    {
        return $this->container->make('request');
    }

    /**
     * @throws BindingResolutionException
     */
    protected function requestContent(): mixed
    {
        if (App::runningInConsole() && !App::runningUnitTests()) {
            if ($runningCommand = Artisan::rootRunningCommand()) {
                return $runningCommand;
            }
        }
        else {
            return $this->request();
        }
        return null;
    }

    /**
     * @throws BindingResolutionException
     */
    protected function context(): array
    {
        return array_filter([
            'request' => $this->requestContent(),
        ]);
    }

    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return parent::shouldReturnJson($request, $e)
            || $request->is(['api', 'api/*']);
    }

    protected function prepareJsonResponse($request, Throwable $e): JsonResponse
    {
        return $this->responseJsonWith($e);
    }

    protected function unauthenticated($request, AuthenticationException $exception): SymfonyResponse
    {
        return $this->shouldReturnJson($request, $exception)
            ? $this->responseJsonWith($exception)
            : redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return $this->responseJsonWith($exception);
    }

    public function renderForConsole($output, Throwable $e)
    {
        Artisan::renderThrowable($e, $output);
    }
}
