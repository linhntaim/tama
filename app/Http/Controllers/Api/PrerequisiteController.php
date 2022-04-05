<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Support\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

class PrerequisiteController extends ApiController
{
    protected array $dataset = [];

    public function index(Request $request): JsonResponse
    {
        return $this
            ->registerDataset($request)
            ->responseJsonWith($this->dataset);
    }

    protected function attachDataset($name, $data): static
    {
        $this->dataset[$name] = $data;
        return $this;
    }

    protected function registerDataset(Request $request): static
    {
        return $this
            ->attachServer($request);
    }

    protected function attachServer(Request $request): static
    {
        return $request->query->has('_server')
            ? $this->attachDataset('server', [
                'max_uploaded_filesize' => UploadedFile::getMaxFilesize(),
            ])
            : $this;
    }
}