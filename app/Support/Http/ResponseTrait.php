<?php

namespace App\Support\Http;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Throwable;

trait ResponseTrait
{
    protected function responseContent(string $content = '', int $status = 200, array $headers = []): Response
    {
        return response($content, $status, $headers);
    }

    protected function responseFileAsContent(string $file, int $status = 200, array $headers = []): Response
    {
        return $this->responseContent(file_get_contents($file), $status, $headers);
    }

    protected function responseView(string $view, array $data = [], array $mergeData = []): View
    {
        return view($view, $data, $mergeData);
    }

    protected function responseJson(ResponsePayload|array|null $data = null, int $status = 200, array $headers = []): JsonResponse
    {
        if ($data instanceof ResponsePayload) {
            $headers = $data->getHeaders();
            $status = $data->getStatusCode();
            $data = $data->toArray();
        }
        return response()->json(
            $data ?? [],
            $status,
            $headers,
            JSON_READABLE
        );
    }

    protected function responseJsonWith(ResponsePayload|bool|array|Throwable|null $source = null, ?Closure $callback = null): JsonResponse
    {
        return $this->responseJson(
            with($source instanceof ResponsePayload ? $source : ResponsePayload::create($source), $callback)
        );
    }

    protected function responseJsonSuccess(?array $data = null, ?int $status = null, array $headers = []): JsonResponse
    {
        if (!is_null($status) && ($status >= 400 || $status < 100)) {
            throw new InvalidArgumentException("$status should be greater than or equal to 100 and less than 400");
        }
        return $this->responseJsonWith(
            $data,
            function (ResponsePayload $responsePayload) use ($status, $headers) {
                return $responsePayload
                    ->setStatusCode($status)
                    ->setHeaders($headers);
            }
        );
    }

    protected function responseJsonFail(string|array|Throwable|null $message = null, ?Throwable $throwable = null, ?int $status = null, array $headers = []): JsonResponse
    {
        if ($message instanceof Throwable) {
            return $this->responseJson(ResponsePayload::create($message));
        }

        if (!is_null($status) && ($status < 400 || $status >= 600)) {
            throw new InvalidArgumentException("$status should be greater than or equal to 400 and less than 600");
        }
        return $this->responseJsonWith(
            is_null($throwable) ? false : $throwable,
            function (ResponsePayload $responsePayload) use ($message, $status, $headers) {
                return $responsePayload
                    ->setMessages($message)
                    ->setStatusCode($status)
                    ->setHeaders($headers);
            }
        );
    }
}