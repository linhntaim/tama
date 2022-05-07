<?php

namespace App\Support\Http;

use App\Support\Exports\Export;
use App\Support\Filesystem\Filers\Filer;
use App\Support\Http\Resources\ResponseResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use JsonSerializable;
use SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse as SymfonyBinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyStreamedResponse;

trait Responses
{
    protected function responseContent(
        Request $request,
        string  $content = '',
        int     $status = 200,
        array   $headers = []
    ): Response
    {
        return response($content, $status, $headers);
    }

    protected function responseFile(
        Request            $request,
        SplFileInfo|string $file,
        array              $headers = []
    ): SymfonyBinaryFileResponse
    {
        return response()->file($file, $headers);
    }

    protected function responseDownload(
        Request            $request,
        SplFileInfo|string $file,
        ?string            $name = null,
        array              $headers = [],
        string             $disposition = 'attachment'
    ): SymfonyBinaryFileResponse
    {
        return response()->download($file, $name, $headers, $disposition);
    }

    protected function responseView(
        Request $request,
        string  $view,
        array   $data = [],
        array   $mergeData = []
    ): View
    {
        return view($view, $data, $mergeData);
    }

    protected function responseJson(
        Request                                            $request,
        JsonResource|Arrayable|JsonSerializable|array|null $data = null,
        int                                                $status = 200,
        array                                              $headers = []
    ): JsonResponse
    {
        return $data instanceof JsonResource
            ? $data->toResponse($request)
            : response()->json(
                $data instanceof Arrayable
                    ? $data->toArray()
                    : (($data instanceof JsonSerializable)
                    ? $data->jsonSerialize()
                    : ($data ?? [])),
                $status,
                $headers,
                JSON_READABLE
            );
    }

    protected function responseResource(
        Request $request,
        mixed   $resource = null,
        mixed   ...$args
    ): JsonResponse
    {
        return $this->responseJson(
            $request,
            take(
                ResponseResource::from($resource, $args[0] ?? null),
                is_callable($callback = ($args[0] ?? null))
                || is_callable($callback = ($args[1] ?? null))
                    ? $callback : null
            )
        );
    }

    protected function responseExport(Export $export, array $headers = []): SymfonyBinaryFileResponse|SymfonyStreamedResponse
    {
        return with($export->disableChunk()(), function (Filer $filer) use ($headers) {
            return take($filer->responseContentDownload($headers), function () use ($filer) {
                $filer->delete();
            });
        });
    }
}