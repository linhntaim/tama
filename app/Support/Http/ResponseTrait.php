<?php

namespace App\Support\Http;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
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

    protected function responseJson(array|ResponsePayload|null $data = null, int $status = 200, array $headers = []): JsonResponse
    {
        if ($data instanceof ResponsePayload) {
            $headers = $data->getHeaders();
            $status = $data->getStatusCode();
            $data = $data->toArray();
        }
        return response()->json(
            $data,
            $status,
            $headers,
            JSON_READABLE
        );
    }

    protected function responseJsonFrom(array|Throwable|null $source = null): JsonResponse
    {
        return $this->responseJson(ResponsePayload::create($source));
    }

    protected function responseJsonOk(array $headers = []): JsonResponse
    {
        return $this->responseJson(null, 200, $headers);
    }
}