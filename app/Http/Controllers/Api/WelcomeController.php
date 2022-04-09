<?php

namespace App\Http\Controllers\Api;

use App\Support\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class WelcomeController extends ApiController
{
    public function index(): JsonResponse
    {
        return $this->responseJsonSuccess([
            'welcome' => 'Welcome',
        ]);
    }
}