<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    protected function response(array $data = [], int $status = 200, array $headers = []): JsonResponse
    {
        return $this->responseJson($data, $status, $headers);
    }
}