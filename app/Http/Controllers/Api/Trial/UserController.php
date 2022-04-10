<?php

namespace App\Http\Controllers\Api\Trial;

use App\Http\Resources\TrialUserResource;
use App\Models\User;
use App\Support\Http\Controllers\ApiController;
use App\Support\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class UserController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        return $this->responseJsonResource(
            $request,
            $request->has('page')
                ? new LengthAwarePaginator(User::factory()->count(10)->make(), 1000, 10, 1)
                : ($request->has('id')
                ? User::factory()->make()
                : User::factory()->count(10)->make()),
            TrialUserResource::class,
            ['resource_class' => TrialUserResource::class]
        );
    }
}