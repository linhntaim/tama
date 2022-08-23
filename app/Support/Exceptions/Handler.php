<?php

namespace App\Support\Exceptions;

use App\Support\Database\Concerns\DatabaseTransaction;
use App\Support\Facades\App;
use App\Support\Facades\Artisan;
use App\Support\Http\Concerns\Requests;
use App\Support\Http\Concerns\Responses;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    use Requests, Responses, DatabaseTransaction;

    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return $this->advancedRequest()->expectsJson();
    }

    protected function requestContent(): mixed
    {
        if (App::runningSolelyInConsole()) {
            if ($runningCommand = Artisan::rootRunningCommand()) {
                return $runningCommand;
            }
        }
        else {
            return request();
        }
        return null;
    }

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

    protected function prepareJsonResponse($request, Throwable $e): JsonResponse
    {
        return $this->responseResource(request(), $e);
    }

    protected function unauthenticated($request, AuthenticationException $exception): SymfonyResponse
    {
        return $this->shouldReturnJson($request, $exception)
            ? $this->responseResource(request(), $exception)
            : redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return $this->responseResource(request(), $exception);
    }

    public function renderForConsole($output, Throwable $e)
    {
        $this->transactionAbort(true);
        Artisan::renderThrowable($e, $output);
    }
}
