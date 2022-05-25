<?php

namespace App\Support\Http\Controllers;

use App\Support\Http\Resources\ModelResource;
use App\Support\Http\Resources\ResponseResource;
use App\Support\Models\Model;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

abstract class ApiController extends Controller
{
    protected function response(Request $request, mixed $resource = null, mixed ...$args): JsonResponse
    {
        return $this->responseResource($request, $resource, ...$args);
    }

    protected function responseModel(
        Request                                                         $request,
        Model|Collection|AbstractPaginator|AbstractCursorPaginator|null $resource = null,
        string                                                          $modelResourceClass = ModelResource::class,
        ?array                                                          $additional = null,
        ?Closure                                                        $callback = null
    ): JsonResponse
    {
        return $this->responseResource(
            $request,
            $resource,
            $modelResourceClass,
            function (ResponseResource $responseResource) use ($additional, $callback) {
                if (is_null($responseResource->resource)) {
                    $responseResource->resource = $additional;
                }
                else {
                    $responseResource->resource->additional($additional ?? []);
                }
                $callback && $callback($responseResource);
            }
        );
    }

    protected function responseSuccess(Request $request, ?array $data = null): JsonResponse
    {
        return $this->responseResource($request, $data ?: true);
    }

    protected function responseFail(Request $request, ?string $message = null): JsonResponse
    {
        return $this->responseResource($request, $message ?: false);
    }
}
