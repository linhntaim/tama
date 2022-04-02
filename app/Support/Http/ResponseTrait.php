<?php

namespace App\Support\Http;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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

    protected function responseJson(array $data = [], int $status = 200, array $headers = []): JsonResponse
    {
        return response_json(
            [
                '_data' => $data,
            ],
            $status,
            $headers
        );
    }
}