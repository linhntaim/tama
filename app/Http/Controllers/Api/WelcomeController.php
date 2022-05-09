<?php

namespace App\Http\Controllers\Api;

use App\Support\Http\Controllers\ApiController;
use App\Support\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

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

    public function ping(Request $request): JsonResponse
    {
        return $this->response($request, [
            'server_time' => microtime(true),
            'server_timezone' => date_default_timezone_get(),
            'upload_max_filesize' => UploadedFile::getMaxFilesize(),
        ]);
    }
}
