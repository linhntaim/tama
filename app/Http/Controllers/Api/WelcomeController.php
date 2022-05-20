<?php

namespace App\Http\Controllers\Api;

use App\Support\Http\Controllers\ApiController;
use App\Support\Http\Request;
use Illuminate\Http\JsonResponse;

class WelcomeController extends ApiController
{
    public function index(Request $request, $path = null): JsonResponse
    {
        if (!is_null($path)) {
            $this->abort404();
        }
        return $this->response($request, [
            'welcome' => 'Welcome',
        ]);
    }
}
