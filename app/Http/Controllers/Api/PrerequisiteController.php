<?php

namespace App\Http\Controllers\Api;

use App\Support\Facades\Client;
use App\Support\Http\Controllers\ApiController;
use App\Support\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PrerequisiteController extends ApiController
{
    protected array $dataset = [];

    public function index(Request $request): JsonResponse
    {
        $dataset = [];
        foreach ($request->input('names', []) as $name) {
            $dataset[$name] = method_exists($this, $method = sprintf('data%s', Str::studly($name)))
                ? $this->{$method}($request)
                : null;
        }
        return $this
            ->response($request, $dataset);
    }

    protected function dataServer(Request $request): array
    {
        return [
            'server_time' => microtime(true),
            'server_timezone' => date_default_timezone_get(),
            'upload_max_filesize' => UploadedFile::getMaxFilesize(),
            'settings' => Client::settings()->toArray(),
        ];
    }
}
