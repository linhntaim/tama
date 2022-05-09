<?php

namespace App\Support\Exceptions;

use App\Support\Database\DatabaseTransaction;
use App\Support\Facades\App;
use App\Support\Facades\Artisan;
use App\Support\Http\Request;
use App\Support\Http\Responses;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    use Responses, DatabaseTransaction;

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
        if (App::runningSolelyInConsole()) {
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

    public function render($request, Throwable $e): SymfonyResponse
    {
        $this->transactionAbort(true);
        return parent::render($request, $e);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function prepareJsonResponse($request, Throwable $e): JsonResponse
    {
        return $this->responseResource($this->request(), $e);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function unauthenticated($request, AuthenticationException $exception): SymfonyResponse
    {
        return $this->shouldReturnJson($request, $exception)
            ? $this->responseResource($this->request(), $exception)
            : redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    /**
     * @throws BindingResolutionException
     */
    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return $this->responseResource($this->request(), $exception);
    }

    public function renderForConsole($output, Throwable $e)
    {
        $this->transactionAbort(true);
        Artisan::renderThrowable($e, $output);
    }
}
